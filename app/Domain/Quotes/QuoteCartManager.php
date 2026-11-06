<?php

namespace App\Domain\Quotes;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\QuoteCart;
use App\Models\QuoteCartItem;
use App\Models\SaleUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

final class QuoteCartManager
{
    public const COOKIE = 'hec_quote';

    public const LIFETIME_DAYS = 30;

    public const MAX_ITEMS = 50;

    private const ATTRIBUTE = 'hec.quote_cart';

    public function current(Request $request): ?QuoteCart
    {
        if ($request->attributes->has(self::ATTRIBUTE)) {
            $cart = $request->attributes->get(self::ATTRIBUTE);

            return $cart instanceof QuoteCart ? $cart : null;
        }

        $token = $request->cookie(self::COOKIE);
        $cart = is_string($token) && strlen($token) === 64 ? QuoteCart::where('token', $token)->first() : null;
        $request->attributes->set(self::ATTRIBUTE, $cart);

        return $cart;
    }

    public function resolve(Request $request, string $locale): QuoteCart
    {
        $cart = $this->current($request);

        if (! $cart instanceof QuoteCart) {
            $cart = QuoteCart::create(['locale' => $locale]);
            $request->attributes->set(self::ATTRIBUTE, $cart);
        }

        $cart->forceFill(['locale' => $locale, 'last_activity_at' => now()])->save();
        Cookie::queue(Cookie::make(self::COOKIE, $cart->token, self::LIFETIME_DAYS * 24 * 60, httpOnly: true, sameSite: 'lax'));

        return $cart;
    }

    public function count(Request $request): int
    {
        return $this->current($request)?->items()->count() ?? 0;
    }

    public function isFull(QuoteCart $cart): bool
    {
        return $cart->items()->count() >= self::MAX_ITEMS;
    }

    public function addProduct(QuoteCart $cart, Product $product, ?ProductVariant $variant): QuoteCartItem
    {
        $existing = QuoteCartItem::where('quote_cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($existing instanceof QuoteCartItem) {
            return $existing;
        }

        $unit = $product->saleUnit;

        return QuoteCartItem::create([
            'quote_cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'unit' => $unit instanceof SaleUnit ? ($unit->getTranslation('name', $cart->locale, false) ?: null) : null,
            'sort' => $this->nextSort($cart),
        ]);
    }

    public function addCustom(QuoteCart $cart, string $text): QuoteCartItem
    {
        return QuoteCartItem::create([
            'quote_cart_id' => $cart->id,
            'free_text' => $text,
            'sort' => $this->nextSort($cart),
        ]);
    }

    public function item(Request $request, int $id): ?QuoteCartItem
    {
        $cart = $this->current($request);

        return $cart instanceof QuoteCart ? QuoteCartItem::where('quote_cart_id', $cart->id)->whereKey($id)->first() : null;
    }

    public function forget(Request $request): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE));
        $request->attributes->set(self::ATTRIBUTE, null);
    }

    private function nextSort(QuoteCart $cart): int
    {
        return ((int) $cart->items()->max('sort')) + 1;
    }
}
