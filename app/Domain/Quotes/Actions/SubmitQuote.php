<?php

namespace App\Domain\Quotes\Actions;

use App\Domain\Quotes\Exceptions\EmptyQuote;
use App\Domain\Quotes\Jobs\SendQuoteNotification;
use App\Domain\Quotes\Support\QuoteCartPresenter;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\QuoteCart;
use App\Models\QuoteCartItem;
use App\Models\QuoteRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class SubmitQuote
{
    public const DISK = 'local';

    public function handle(?QuoteCart $cart, array $data, array $files, string $locale, array $context): QuoteRequest
    {
        $existing = $this->existing($data['idempotency_key']);

        if ($existing instanceof QuoteRequest) {
            return $existing;
        }

        $items = QuoteCartPresenter::usable($cart);

        if (! $cart instanceof QuoteCart || $items->isEmpty()) {
            throw new EmptyQuote;
        }

        $stored = $this->storeFiles($files);

        try {
            $quote = DB::transaction(function () use ($cart, $data, $items, $stored, $locale, $context) {
                $quote = QuoteRequest::create([
                    'idempotency_key' => $data['idempotency_key'],
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'company' => $data['company'] ?? null,
                    'email' => $data['email'] ?? null,
                    'city' => $data['city'],
                    'fulfillment' => $data['fulfillment'],
                    'needed_by' => $data['needed_by'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'privacy_accepted_at' => now(),
                    'locale' => $locale,
                    'source' => $context['source'] ?? null,
                    'ip_hash' => $context['ip_hash'] ?? null,
                ]);

                foreach ($items->values() as $index => $item) {
                    $quote->items()->create($this->snapshot($item, $locale, $index + 1));
                }

                foreach ($stored as $file) {
                    $quote->attachments()->create($file);
                }

                $cart->delete();

                SendQuoteNotification::dispatch($quote->id)->afterCommit();

                return $quote;
            });
        } catch (UniqueConstraintViolationException $exception) {
            $this->discard($stored);
            $existing = $this->existing($data['idempotency_key']);

            if ($existing instanceof QuoteRequest) {
                return $existing;
            }

            throw $exception;
        } catch (Throwable $exception) {
            $this->discard($stored);

            throw $exception;
        }

        return $quote->refresh();
    }

    private function existing(string $key): ?QuoteRequest
    {
        return QuoteRequest::withTrashed()->where('idempotency_key', $key)->first();
    }

    private function snapshot(QuoteCartItem $item, string $locale, int $sort): array
    {
        $product = $item->product;
        $variant = $item->variant;

        $base = [
            'thickness' => $item->thickness,
            'dimensions' => $item->dimensions,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'notes' => $item->notes,
            'sort' => $sort,
        ];

        if (! $product instanceof Product) {
            return [...$base, 'is_custom' => true, 'product_name' => Str::limit((string) $item->free_text, 250, '')];
        }

        return [
            ...$base,
            'is_custom' => false,
            'product_id' => $product->id,
            'product_variant_id' => $variant instanceof ProductVariant ? $variant->id : null,
            'product_name' => Str::limit((string) $product->getTranslation('name', $locale), 250, ''),
            'variant_label' => $variant instanceof ProductVariant ? $variant->labelFor($locale) : null,
            'product_url' => QuoteCartPresenter::productUrl($product, $locale),
        ];
    }

    private function storeFiles(array $files): array
    {
        $stored = [];

        try {
            foreach ($files as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $mime = (string) $file->getMimeType();
                $extension = match ($mime) {
                    'application/pdf' => 'pdf',
                    'image/png' => 'png',
                    'image/jpeg' => 'jpg',
                    default => throw new RuntimeException('Attachment type is not allowed.'),
                };
                $path = $file->storeAs('quotes/'.now()->format('Y/m'), Str::uuid().'.'.$extension, self::DISK);

                if ($path === false) {
                    throw new RuntimeException('Attachment could not be stored.');
                }

                $stored[] = [
                    'disk' => self::DISK,
                    'path' => $path,
                    'original_name' => Str::limit(basename($file->getClientOriginalName()), 200, ''),
                    'mime_type' => $mime,
                    'size_bytes' => (int) $file->getSize(),
                    'sha256' => (string) hash_file('sha256', (string) $file->getRealPath()),
                ];
            }
        } catch (Throwable $exception) {
            $this->discard($stored);

            throw $exception;
        }

        return $stored;
    }

    private function discard(array $stored): void
    {
        foreach ($stored as $file) {
            Storage::disk($file['disk'])->delete($file['path']);
        }
    }
}
