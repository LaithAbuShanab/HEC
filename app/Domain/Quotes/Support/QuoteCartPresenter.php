<?php

namespace App\Domain\Quotes\Support;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\QuoteCart;
use App\Models\QuoteCartItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class QuoteCartPresenter
{
    public static function items(?QuoteCart $cart, string $locale): array
    {
        return self::usable($cart)
            ->map(fn (QuoteCartItem $item) => self::item($item, $locale))
            ->values()
            ->all();
    }

    public static function usable(?QuoteCart $cart): Collection
    {
        if (! $cart instanceof QuoteCart) {
            return collect();
        }

        $items = QuoteCartItem::where('quote_cart_id', $cart->id)
            ->orderBy('sort')
            ->with(['product' => fn ($query) => $query->with(['media', 'primaryCategory', 'variants']), 'variant'])
            ->get();

        $stale = $items->reject(fn (QuoteCartItem $item) => self::isUsable($item));

        if ($stale->isNotEmpty()) {
            QuoteCartItem::whereKey($stale->modelKeys())->delete();
        }

        return $items->filter(fn (QuoteCartItem $item) => self::isUsable($item))->values();
    }

    public static function isUsable(QuoteCartItem $item): bool
    {
        if ($item->product_id === null) {
            return filled($item->free_text);
        }

        $product = $item->product;

        return $product instanceof Product
            && $product->review_status === ReviewStatus::Published
            && $product->primaryCategory instanceof Category
            && $product->primaryCategory->review_status === ReviewStatus::Published;
    }

    public static function productUrl(Product $product, string $locale): ?string
    {
        $category = $product->primaryCategory;

        return $category instanceof Category ? url("{$locale}/products/{$category->slug}/{$product->slug}") : null;
    }

    public static function publishedProducts(): Builder
    {
        return Product::published()
            ->with(['saleUnit', 'variants', 'primaryCategory'])
            ->whereHas('primaryCategory', fn (Builder $query) => $query->where('review_status', ReviewStatus::Published));
    }

    private static function item(QuoteCartItem $item, string $locale): array
    {
        $product = $item->product;

        if (! $product instanceof Product) {
            return [
                'id' => $item->id,
                'custom' => true,
                'name' => (string) $item->free_text,
                'href' => null,
                'image' => null,
                'category' => null,
                'variants' => [],
                ...self::fields($item),
            ];
        }

        return [
            'id' => $item->id,
            'custom' => false,
            'name' => (string) $product->getTranslation('name', $locale),
            'href' => self::productUrl($product, $locale),
            'image' => $product->imageFor('main', $locale),
            'category' => $product->primaryCategory instanceof Category ? $product->primaryCategory->slug : null,
            'variants' => $product->variants
                ->filter(fn (Model $variant) => $variant instanceof ProductVariant)
                ->map(fn (ProductVariant $variant) => ['id' => $variant->id, 'label' => $variant->labelFor($locale) ?? (string) $variant->id])
                ->values()
                ->all(),
            ...self::fields($item),
        ];
    }

    private static function fields(QuoteCartItem $item): array
    {
        return [
            'variant' => $item->product_variant_id,
            'thickness' => $item->thickness,
            'dimensions' => $item->dimensions,
            'quantity' => $item->quantity === null ? null : rtrim(rtrim((string) $item->quantity, '0'), '.'),
            'unit' => $item->unit,
            'notes' => $item->notes,
        ];
    }
}
