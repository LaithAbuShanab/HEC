<?php

namespace App\Domain\Quotes\Mail;

use App\Filament\Admin\Resources\QuoteRequests\QuoteRequestResource;
use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequestReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly QuoteRequest $quote)
    {
        $this->locale('ar');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('site.quote.mail.subject', ['reference' => $this->quote->reference], 'ar'));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.quote-request', with: [
            'quote' => $this->quote,
            'adminUrl' => QuoteRequestResource::getUrl('view', ['record' => $this->quote], panel: 'admin'),
        ]);
    }
}
