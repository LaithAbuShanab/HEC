<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpecValue extends Model
{
    protected $fillable = ['product_id', 'spec_attribute_id', 'value', 'value_numeric'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'value_numeric' => 'decimal:4',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(SpecAttribute::class, 'spec_attribute_id');
    }

    public function valueFor(string $locale): ?string
    {
        return $this->value[$locale] ?? $this->value['ar'] ?? null;
    }
}
