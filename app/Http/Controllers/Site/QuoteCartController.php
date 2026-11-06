<?php

namespace App\Http\Controllers\Site;

use App\Domain\Quotes\QuoteCartManager;
use App\Domain\Quotes\Support\QuoteCartPresenter;
use App\Http\Requests\Site\QuoteItemRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\QuoteCartItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuoteCartController
{
    public function __construct(private readonly QuoteCartManager $carts) {}

    public function store(Request $request, string $locale): RedirectResponse
    {
        $data = $request->validate([
            'product' => ['required_without:free_text', 'nullable', 'string', 'max:200'],
            'variant' => ['nullable', 'integer'],
            'free_text' => ['required_without:product', 'nullable', 'string', 'min:2', 'max:250'],
        ], [
            'free_text.*' => __('site.quote.errors.free_text'),
            'product.*' => __('site.quote.errors.product'),
        ]);

        $product = null;
        $variant = null;

        if (filled($data['product'] ?? null)) {
            $product = QuoteCartPresenter::publishedProducts()->where('slug', $data['product'])->first();

            if (! $product instanceof Product) {
                throw ValidationException::withMessages(['product' => __('site.quote.errors.product')]);
            }

            $variant = $this->variantFor($product, $data['variant'] ?? null);
        }

        $cart = $this->carts->resolve($request, $locale);

        if ($this->carts->isFull($cart)) {
            throw ValidationException::withMessages(['items' => __('site.quote.errors.full', ['max' => QuoteCartManager::MAX_ITEMS])]);
        }

        $product instanceof Product
            ? $this->carts->addProduct($cart, $product, $variant)
            : $this->carts->addCustom($cart, trim((string) $data['free_text']));

        return back()->with('quote_added', $product instanceof Product ? (string) $product->getTranslation('name', $locale) : trim((string) $data['free_text']));
    }

    public function update(QuoteItemRequest $request, string $locale, int $item): RedirectResponse
    {
        $model = $this->find($request, $item);
        $data = $request->validated();
        $product = $model->product;

        if ($product instanceof Product && array_key_exists('variant', $data)) {
            $model->product_variant_id = $this->variantFor($product, $data['variant'])?->id;
        }

        $model->fill(collect($data)->only(['thickness', 'dimensions', 'quantity', 'unit', 'notes'])->map(fn ($value) => is_string($value) ? (trim($value) ?: null) : $value)->all());
        $model->save();
        $model->cart?->touch();

        return back();
    }

    public function destroy(Request $request, string $locale, int $item): RedirectResponse
    {
        $this->find($request, $item)->delete();

        return back()->with('quote_removed', true);
    }

    private function find(Request $request, int $id): QuoteCartItem
    {
        $item = $this->carts->item($request, $id);
        abort_unless($item instanceof QuoteCartItem, 404);

        return $item;
    }

    private function variantFor(Product $product, mixed $id): ?ProductVariant
    {
        if ($id === null || $id === '') {
            return null;
        }

        $variant = $product->variants()->whereKey((int) $id)->first();

        if (! $variant instanceof ProductVariant) {
            throw ValidationException::withMessages(['variant' => __('site.quote.errors.variant')]);
        }

        return $variant;
    }
}
