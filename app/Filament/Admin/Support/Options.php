<?php

namespace App\Filament\Admin\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\SaleUnit;
use App\Models\SpecAttribute;

final class Options
{
    public static function categories(): array
    {
        return Category::query()->orderBy('sort')->get()
            ->mapWithKeys(fn (Category $category) => [$category->id => $category->getTranslation('name', 'ar')])
            ->all();
    }

    public static function saleUnits(): array
    {
        return SaleUnit::query()->orderBy('sort')->get()
            ->mapWithKeys(fn (SaleUnit $unit) => [$unit->id => $unit->getTranslation('name', 'ar')])
            ->all();
    }

    public static function specAttributes(): array
    {
        return SpecAttribute::query()->orderBy('sort')->get()
            ->mapWithKeys(fn (SpecAttribute $attribute) => [
                $attribute->id => $attribute->getTranslation('name', 'ar').($attribute->unit ? " ({$attribute->unit})" : ''),
            ])
            ->all();
    }

    public static function products(?int $except = null): array
    {
        return Product::query()->when($except, fn ($query) => $query->whereKeyNot($except))->orderBy('sort')->get()
            ->mapWithKeys(fn (Product $product) => [$product->id => $product->getTranslation('name', 'ar')])
            ->all();
    }
}
