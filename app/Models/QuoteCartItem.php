<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteCartItem extends Model
{
    protected $fillable = [
        'quote_cart_id', 'product_id', 'product_variant_id', 'free_text',
        'thickness', 'dimensions', 'quantity', 'unit', 'notes', 'sort',
    ];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3'];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(QuoteCart::class, 'quote_cart_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function isCustom(): bool
    {
        return $this->product_id === null;
    }
}
