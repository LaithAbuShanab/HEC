<?php

namespace App\Http\Controllers\Admin;

use App\Models\QuoteAttachment;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadQuoteAttachmentController
{
    public function __invoke(Request $request, QuoteAttachment $attachment): StreamedResponse
    {
        $quote = QuoteRequest::withTrashed()->find($attachment->quote_request_id);

        abort_unless($quote !== null && $request->user()?->can('view', $quote), 403);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
