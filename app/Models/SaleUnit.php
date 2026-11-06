<?php

namespace App\Models;

use App\Models\Contracts\Translatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SaleUnit extends Model implements Translatable
{
    use HasTranslations;

    protected $fillable = ['key', 'name', 'sort'];

    public array $translatable = ['name'];

    public function usageCount(): int
    {
        return Product::withTrashed()->where('sale_unit_id', $this->id)->count()
            + ProductVariant::where('sale_unit_id', $this->id)->count();
    }
}
