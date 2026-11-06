<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class QuoteAttachment extends Model
{
    public const ALLOWED_MIME_TYPES = ['application/pdf', 'image/jpeg', 'image/png'];

    public const MAX_SIZE_KB = 10240;

    public const MAX_FILES = 3;

    protected $fillable = ['quote_request_id', 'disk', 'path', 'original_name', 'mime_type', 'size_bytes', 'sha256'];

    protected $hidden = ['disk', 'path'];

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function temporaryUrl(int $minutes = 10): string
    {
        return Storage::disk($this->disk)->temporaryUrl($this->path, now()->addMinutes($minutes));
    }
}
