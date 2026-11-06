<?php

namespace App\Http\Controllers\Site;

use App\Domain\Catalog\CatalogFilters;
use App\Domain\Catalog\Enums\Availability;
use App\Domain\Catalog\Enums\ReviewStatus;
use App\Domain\Catalog\Presenters\ProductPresenter;
use App\Domain\Seo\SeoData;
use App\Domain\Seo\SeoTitle;
use App\Models\Category;
use App\Models\Guide;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController
{
    public const PER_PAGE = 12;

    public function index(Request $request): Response
    {
        $locale = app()->getLocale();
        $products = $this->publishedProducts()->get();
        $page = $this->paginate($products, $request);

        $seo = SeoData::make(SeoTitle::section(__('seo.products_title'), $locale), __('seo.products_description'))
            ->noindex($page->currentPage() > $page->lastPage())
            ->schema($this->breadcrumbSchema([[__('site.nav.home'), url($locale)], [__('site.nav.products'), url("{$locale}/products")]]));

        return Inertia::render('products/index', [
            'categories' => $this->categoryCards($locale),
            'products' => $this->paginatedCards($page, $locale),
            'seo' => $seo->toArray($request),
        ]);
    }

    public function category(Request $request, string $locale, string $category): Response
    {
        $model = Category::published()->where('slug', $category)->firstOrFail();
        $filters = CatalogFilters::fromRequest($request, $locale);

        $products = $this->publishedProducts()
            ->where(fn (Builder $query) => $query
                ->where('primary_category_id', $model->id)
                ->orWhereHas('categories', fn (Builder $categories) => $categories->whereKey($model->id)))
            ->get();

        $filtered = $filters->apply($products);
        $page = $this->paginate($filtered, $request);
        $name = (string) $model->getTranslation('name', $locale);
        $description = $model->getTranslation('description', $locale, false) ?: null;
        $categoryUrl = url("{$locale}/products/{$model->slug}");

        $seo = SeoData::make(SeoTitle::section($name, $locale), $description)
            ->overrides($model->seo, $locale)
            ->image($model->imageFor('cover', $locale)['src'] ?? null)
            ->noindex($filters->isFiltered())
            ->schema($this->breadcrumbSchema([[__('site.nav.home'), url($locale)], [__('site.nav.products'), url("{$locale}/products")], [$name, $categoryUrl]]));

        if ($filters->isFiltered()) {
            $seo->canonical($categoryUrl);
        }

        return Inertia::render('products/category', [
            'category' => [
                'slug' => $model->slug,
                'name' => $name,
                'description' => $description,
                'image' => $model->imageFor('cover', $locale),
            ],
            'products' => $this->paginatedCards($page, $locale),
            'facets' => $filters->facets($products),
            'filtered' => $filters->isFiltered(),
            'total' => $filtered->count(),
            'resultsLabel' => trans_choice('site.filters.results', $filtered->count(), ['count' => $filtered->count()]),
            'siblings' => collect($this->categoryCards($locale))->reject(fn (array $card) => $card['slug'] === $model->slug)->values()->all(),
            'guides' => $this->guidesFor($model, null, $locale),
            'seo' => $seo->toArray($request),
        ]);
    }

    public function product(Request $request, string $locale, string $category, string $product): Response|RedirectResponse
    {
        $model = $this->publishedProducts()
            ->with(['saleUnit', 'specValues.attribute', 'related' => fn ($query) => $query->where('review_status', ReviewStatus::Published)->with(['media', 'primaryCategory'])])
            ->where('slug', $product)
            ->first();

        if (! $model instanceof Product) {
            abort(404);
        }

        $primary = $model->primaryCategory;

        if (! $primary instanceof Category || ! $primary->isPublished()) {
            abort(404);
        }

        if ($primary->slug !== $category) {
            return redirect()->to(url("{$locale}/products/{$primary->slug}/{$model->slug}"), 301);
        }

        $detail = ProductPresenter::detail($model, $locale);
        $categoryName = (string) $primary->getTranslation('name', $locale);
        $productUrl = url("{$locale}/products/{$primary->slug}/{$model->slug}");

        $seo = SeoData::make(SeoTitle::product($detail['name'], $locale), $detail['summary'] ?? $detail['uses'])
            ->overrides($model->seo, $locale)
            ->type('product')
            ->image($detail['image']['src'] ?? null)
            ->schema($this->breadcrumbSchema([
                [__('site.nav.home'), url($locale)],
                [__('site.nav.products'), url("{$locale}/products")],
                [$categoryName, url("{$locale}/products/{$primary->slug}")],
                [$detail['name'], $productUrl],
            ]))
            ->schema($this->productSchema($model, $detail, $categoryName, $productUrl));

        return Inertia::render('products/show', [
            'product' => $detail,
            'category' => ['slug' => $primary->slug, 'name' => $categoryName],
            'related' => $this->relatedFor($model, $locale),
            'guides' => $this->guidesFor($primary, $model, $locale),
            'seo' => $seo->toArray($request),
        ]);
    }

    private function publishedProducts(): Builder
    {
        return Product::published()
            ->with(['media', 'primaryCategory', 'variants'])
            ->whereHas('primaryCategory', fn (Builder $query) => $query->where('review_status', ReviewStatus::Published))
            ->orderBy('sort');
    }

    private function paginate(Collection $items, Request $request): LengthAwarePaginator
    {
        $page = max(1, (int) $request->query('page', '1'));

        return new LengthAwarePaginator(
            $items->forPage($page, self::PER_PAGE)->values(),
            $items->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );
    }

    private function paginatedCards(LengthAwarePaginator $page, string $locale): array
    {
        return [
            'data' => collect($page->items())->map(fn (Product $product) => ProductPresenter::card($product, $locale))->all(),
            'currentPage' => $page->currentPage(),
            'lastPage' => $page->lastPage(),
            'links' => collect(range(1, max(1, $page->lastPage())))->map(fn (int $number) => [
                'page' => $number,
                'url' => $page->url($number),
                'active' => $number === $page->currentPage(),
            ])->all(),
            'prev' => $page->previousPageUrl(),
            'next' => $page->nextPageUrl(),
        ];
    }

    private function categoryCards(string $locale): array
    {
        $counts = Product::published()->selectRaw('primary_category_id, count(*) as total')->groupBy('primary_category_id')->pluck('total', 'primary_category_id');

        return Category::published()->with('media')->whereNull('parent_id')->orderBy('sort')->get()
            ->map(fn (Category $category) => [
                'slug' => $category->slug,
                'name' => $category->getTranslation('name', $locale),
                'description' => $category->getTranslation('description', $locale, false) ?: null,
                'image' => $category->imageFor('cover', $locale),
                'productsLabel' => trans_choice('site.home.products_count', (int) ($counts[$category->id] ?? 0), ['count' => (int) ($counts[$category->id] ?? 0)], $locale),
            ])->all();
    }

    private function relatedFor(Product $product, string $locale): array
    {
        $related = $product->related;

        if ($related->isEmpty()) {
            $related = $this->publishedProducts()
                ->where('primary_category_id', $product->primary_category_id)
                ->whereKeyNot($product->id)
                ->limit(4)
                ->get();
        }

        return $related
            ->filter(fn (Model $item) => $item instanceof Product)
            ->take(4)
            ->map(fn (Product $item) => ProductPresenter::card($item, $locale))
            ->values()
            ->all();
    }

    private function guidesFor(Category $category, ?Product $product, string $locale): array
    {
        return Guide::published()
            ->where(fn (Builder $query) => $query
                ->whereHas('categories', fn (Builder $categories) => $categories->whereKey($category->id))
                ->when($product, fn (Builder $inner) => $inner->orWhereHas('products', fn (Builder $products) => $products->whereKey($product?->id))))
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn (Guide $guide) => [
                'slug' => $guide->slug,
                'title' => $guide->getTranslation('title', $locale),
                'excerpt' => $guide->getTranslation('excerpt', $locale, false) ?: null,
            ])->all();
    }

    private function breadcrumbSchema(array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item[0],
                'item' => $item[1],
            ])->all(),
        ];
    }

    private function productSchema(Product $product, array $detail, string $categoryName, string $url): array
    {
        $schema = [
            '@type' => 'Product',
            'name' => $detail['name'],
            'url' => $url,
            'category' => $categoryName,
            'description' => $detail['summary'] ?? strip_tags((string) $detail['description']) ?: null,
            'image' => array_values(array_filter(array_map(fn (array $image) => $image['src'] ?? null, $detail['gallery']))),
        ];

        if (filled($product->brand)) {
            $schema['brand'] = ['@type' => 'Brand', 'name' => $product->brand];
        }

        if ($product->price !== null) {
            $offer = [
                '@type' => 'Offer',
                'price' => (string) $product->price,
                'priceCurrency' => $product->price_currency,
                'url' => $url,
            ];

            $availability = match ($product->availability) {
                Availability::InStock => 'https://schema.org/InStock',
                Availability::OnOrder => 'https://schema.org/PreOrder',
                Availability::TemporarilyUnavailable => 'https://schema.org/OutOfStock',
                Availability::NeedsConfirmation => null,
            };

            if ($availability !== null) {
                $offer['availability'] = $availability;
            }

            $schema['offers'] = $offer;
        }

        return array_filter($schema, fn ($value) => $value !== null && $value !== []);
    }
}
