<?php

namespace App\Models;

use App\Domain\Catalog\Enums\Availability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class ProductVariant extends Model
{
    use HasTranslations;

    protected $fillable = [
        'product_id', 'sku', 'label', 'standard', 'thickness_mm', 'width_mm', 'length_mm',
        'diameter_mm', 'nominal_size', 'sale_unit_id', 'availability', 'sort',
    ];

    public array $translatable = ['label'];

    protected function casts(): array
    {
        return [
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
}
