<?php

namespace App\Domain\Catalog\Presenters;

use App\Domain\Catalog\Enums\Availability;
use App\Domain\Catalog\Enums\ProductCondition;
use App\Domain\Shared\Support\RichText;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecValue;
use App\Models\ProductVariant;
use App\Models\SaleUnit;
use App\Models\SpecAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class ProductPresenter
{
    public static function card(Product $product, string $locale): array
    {
        return [
            'slug' => $product->slug,
            'category' => $product->primaryCategory instanceof Category ? $product->primaryCategory->slug : null,
            'categoryName' => $product->primaryCategory instanceof Category ? $product->primaryCategory->getTranslation('name', $locale) : null,
            'name' => $product->getTranslation('name', $locale),
            'summary' => $product->getTranslation('short_description', $locale, false) ?: null,
            'image' => $product->imageFor('main', $locale),
            'availability' => self::availability($product->availability, $locale),
            'condition' => $product->condition === ProductCondition::Unconfirmed ? null : $product->condition->label($locale),
            'variants' => $product->relationLoaded('variants') ? $product->variants->count() : null,
        ];
    }

    public static function detail(Product $product, string $locale): array
    {
        $category = $product->primaryCategory;
        $saleUnit = $product->saleUnit;

        return [
            ...self::card($product, $locale),
            'id' => $product->uuid,
            'description' => RichText::clean($product->getTranslation('description', $locale, false)),
            'uses' => $product->getTranslation('uses', $locale, false) ?: null,
            'gallery' => $product->getMedia('main')->merge($product->getMedia('gallery'))
                ->map(fn (Media $media) => Product::presentImage($media, $locale))
                ->values()
                ->all(),
            'specs' => self::specs($product, $locale, $saleUnit instanceof SaleUnit ? $saleUnit : null),
            'variants' => $product->variants
                ->filter(fn (Model $variant) => $variant instanceof ProductVariant)
                ->map(fn (ProductVariant $variant) => self::variant($variant, $locale))
                ->values()
                ->all(),
            'saleUnit' => $saleUnit instanceof SaleUnit ? $saleUnit->getTranslation('name', $locale) : null,
            'availabilityUpdatedAt' => $product->availability_updated_at?->toDateString(),
            'documents' => $product->getMedia('documents')
                ->map(fn (Media $media) => [
                    'name' => $media->name,
                    'url' => $media->getUrl(),
                    'size' => Number::fileSize($media->size),
                ])
                ->values()
                ->all(),
            'hasPrice' => $product->price !== null,
            'price' => $product->price === null ? null : [
                'amount' => (float) $product->price,
                'currency' => $product->price_currency,
            ],
            'categoryName' => $category instanceof Category ? $category->getTranslation('name', $locale) : null,
        ];
    }

    public static function availability(Availability $availability, string $locale): array
    {
        return ['value' => $availability->value, 'label' => $availability->label($locale)];
    }

    private static function specs(Product $product, string $locale, ?SaleUnit $saleUnit): array
    {
        $rows = [
            ['key' => 'material', 'value' => $product->getTranslation('material', $locale, false) ?: null],
            ['key' => 'grade', 'value' => $product->grade],
            ['key' => 'standard', 'value' => $product->standard],
            ['key' => 'origin', 'value' => $product->getTranslation('origin', $locale, false) ?: null],
            ['key' => 'brand', 'value' => $product->brand],
            ['key' => 'model', 'value' => $product->model],
            ['key' => 'condition', 'value' => $product->condition === ProductCondition::Unconfirmed ? null : $product->condition->label($locale)],
            ['key' => 'sale_unit', 'value' => $saleUnit?->getTranslation('name', $locale)],
        ];

        $rows = array_map(fn (array $row) => [...$row, 'label' => __("site.specs.{$row['key']}", locale: $locale), 'unit' => null], $rows);

        foreach ($product->specValues as $value) {
            if (! $value instanceof ProductSpecValue || ! $value->attribute instanceof SpecAttribute) {
                continue;
            }

            $rows[] = [
                'key' => $value->attribute->key,
                'label' => $value->attribute->getTranslation('name', $locale),
                'value' => $value->valueFor($locale) ?? ($value->value_numeric === null ? null : rtrim(rtrim((string) $value->value_numeric, '0'), '.')),
                'unit' => $value->attribute->unit,
            ];
        }

        return array_values(array_filter($rows, fn (array $row) => filled($row['value'])));
    }

    private static function variant(ProductVariant $variant, string $locale): array
    {
        $number = fn ($value) => $value === null ? null : rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');

        return [
            'id' => $variant->id,
            'label' => $variant->labelFor($locale),
            'standard' => $variant->standard,
            'thickness' => $number($variant->thickness_mm),
            'width' => $number($variant->width_mm),
            'length' => $number($variant->length_mm),
            'diameter' => $number($variant->diameter_mm),
            'nominalSize' => $variant->nominal_size,
            'availability' => $variant->availability instanceof Availability ? self::availability($variant->availability, $locale) : null,
        ];
    }
}
