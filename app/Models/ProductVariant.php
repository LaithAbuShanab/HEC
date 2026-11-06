<?php

namespace App\Models;

use App\Domain\Catalog\Enums\Availability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'label', 'standard', 'thickness_mm', 'width_mm', 'length_mm',
        'diameter_mm', 'nominal_size', 'sale_unit_id', 'availability', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'label' => 'array',
            'availability' => Availability::class,
            'thickness_mm' => 'decimal:3',
            'width_mm' => 'decimal:2',
            'length_mm' => 'decimal:2',
            'diameter_mm' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function saleUnit(): BelongsTo
    {
        return $this->belongsTo(SaleUnit::class);
    }

    public function labelFor(string $locale): ?string
    {
        return $this->label[$locale] ?? $this->label['ar'] ?? null;
    }
}
