<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequestItem extends Model
{
    protected $fillable = [
        'quote_request_id', 'product_id', 'product_variant_id', 'product_name', 'variant_label', 'product_url',
        'is_custom', 'thickness', 'dimensions', 'quantity', 'unit', 'notes', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'is_custom' => 'boolean',
            'quantity' => 'decimal:3',
        ];
    }

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
