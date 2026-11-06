<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class SpecAttribute extends Model
{
    use HasTranslations;

    protected $fillable = ['key', 'name', 'unit', 'type', 'is_filterable', 'sort'];

    public array $translatable = ['name'];

    protected function casts(): array
    {
        return ['is_filterable' => 'boolean'];
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductSpecValue::class);
    }
}
