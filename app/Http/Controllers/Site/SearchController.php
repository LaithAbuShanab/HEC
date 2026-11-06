<?php

namespace App\Http\Controllers\Site;

use App\Domain\Catalog\CatalogSearch;
use App\Domain\Catalog\Presenters\ProductPresenter;
use App\Domain\Seo\SeoData;
use App\Domain\Seo\SeoTitle;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController
{
    public function __invoke(Request $request, CatalogSearch $search): Response
    {
        $locale = app()->getLocale();
        $query = mb_substr(trim((string) $request->query('q', '')), 0, CatalogSearch::MAX_QUERY_LENGTH);

        $products = $query === '' ? collect() : $search->products($query);
        $categories = $query === '' ? collect() : $search->categories($query);

        if ($query !== '') {
            $search->log($query, $locale, $products->count() + $categories->count());
        }

        return Inertia::render('search', [
            'query' => $query,
            'products' => $products->map(fn (Product $product) => ProductPresenter::card($product, $locale))->values()->all(),
            'categories' => $categories->map(fn (Category $category) => [
                'slug' => $category->slug,
                'name' => $category->getTranslation('name', $locale),
            ])->values()->all(),
            'seo' => SeoData::make(SeoTitle::section(__('seo.search_title'), $locale))->noindex()->toArray($request),
        ]);
    }
}
