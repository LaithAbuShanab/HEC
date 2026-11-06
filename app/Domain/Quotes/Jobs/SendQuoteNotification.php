<?php

namespace App\Domain\Quotes\Jobs;

use App\Domain\Quotes\Enums\NotificationStatus;
use App\Domain\Quotes\Mail\QuoteRequestReceived;
use App\Models\QuoteRequest;
use App\Settings\CompanySettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SendQuoteNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 6;

    public int $timeout = 60;

    public function __construct(public readonly int $quoteId) {}

    public function backoff(): array
    {
        return [60, 300, 900, 1800, 3600];
    }

    public function handle(CompanySettings $settings): void
    {
        $quote = QuoteRequest::withTrashed()->with(['items', 'attachments'])->find($this->quoteId);

        if (! $quote instanceof QuoteRequest || $quote->notification_status === NotificationStatus::Sent) {
            return;
        }

        $quote->increment('notification_attempts');
        $recipient = self::recipient($settings);

        try {
            if ($recipient === null) {
                throw new RuntimeException('No quote notification email is configured.');
            }

            Mail::to($recipient)->send(new QuoteRequestReceived($quote));
        } catch (Throwable $exception) {
            $quote->forceFill(['last_notification_error' => Str::limit($exception->getMessage(), 1000)])->saveQuietly();

            throw $exception;
        }

        $quote->forceFill([
            'notification_status' => NotificationStatus::Sent,
            'notified_at' => now(),
            'last_notification_error' => null,
        ])->saveQuietly();
    }

    public function failed(?Throwable $exception): void
    {
        QuoteRequest::withTrashed()->whereKey($this->quoteId)->update([
            'notification_status' => NotificationStatus::Failed,
            'last_notification_error' => Str::limit($exception?->getMessage() ?? 'Unknown error', 1000),
        ]);
    }

    public static function recipient(CompanySettings $settings): ?string
    {
        $email = $settings->quote_notification_email ?: config('mail.quote_recipient');

        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
}
