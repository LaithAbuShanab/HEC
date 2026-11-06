<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class ProductSpecValue extends Model
{
    use HasTranslations;

    protected $fillable = ['product_id', 'spec_attribute_id', 'value', 'value_numeric'];

    public array $translatable = ['value'];

    protected function casts(): array
    {
        return ['value_numeric' => 'decimal:4'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(SpecAttribute::class, 'spec_attribute_id');
    }
}
