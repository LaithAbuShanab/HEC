<?php

namespace App\Domain\Site;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Industry;
use App\Models\Product;
use App\Models\Service;
use App\Models\WorkItem;

final class HomePageData
{
    public function forLocale(string $locale): array
    {
        $counts = Product::published()
            ->selectRaw('primary_category_id, count(*) as total')
            ->groupBy('primary_category_id')
            ->pluck('total', 'primary_category_id');

        return [
            'categories' => Category::published()->with('media')->whereNull('parent_id')->orderBy('sort')->get()
                ->map(fn (Category $category) => [
                    'slug' => $category->slug,
                    'name' => $category->getTranslation('name', $locale),
                    'description' => $category->getTranslation('description', $locale, false) ?: null,
                    'image' => $category->getFirstMediaUrl('cover') ?: null,
                    'products' => (int) ($counts[$category->id] ?? 0),
                    'products_label' => trans_choice('site.home.products_count', (int) ($counts[$category->id] ?? 0), ['count' => (int) ($counts[$category->id] ?? 0)], $locale),
                ])->all(),
            'featured' => Product::published()->with(['media', 'primaryCategory'])->where('is_featured', true)->orderBy('sort')->limit(8)->get()
                ->map(fn (Product $product) => [
                    'slug' => $product->slug,
                    'category' => $product->primaryCategory instanceof Category ? $product->primaryCategory->slug : null,
                    'name' => $product->getTranslation('name', $locale),
                    'summary' => $product->getTranslation('short_description', $locale, false) ?: null,
                    'image' => $product->getFirstMediaUrl('main') ?: null,
                    'availability' => $product->availability->label($locale),
                ])->all(),
            'services' => Service::published()->with('media')->orderBy('sort')->limit(6)->get()
                ->map(fn (Service $service) => [
                    'slug' => $service->slug,
                    'title' => $service->getTranslation('title', $locale),
                    'summary' => $service->getTranslation('summary', $locale, false) ?: null,
                    'image' => $service->getFirstMediaUrl('cover') ?: null,
                ])->all(),
            'industries' => Industry::published()->orderBy('sort')->get()
                ->map(fn (Industry $industry) => [
                    'slug' => $industry->slug,
                    'title' => $industry->getTranslation('title', $locale),
                    'summary' => $industry->getTranslation('summary', $locale, false) ?: null,
                ])->all(),
            'work' => WorkItem::published()->with('media')->orderBy('sort')->limit(3)->get()
                ->map(fn (WorkItem $item) => [
                    'slug' => $item->slug,
                    'title' => $item->getTranslation('title', $locale),
                    'image' => $item->getFirstMediaUrl('gallery') ?: null,
                ])->all(),
            'faqs' => Faq::published()->where('show_on_home', true)->orderBy('sort')->limit(6)->get()
                ->map(fn (Faq $faq) => [
                    'question' => $faq->getTranslation('question', $locale),
                    'answer' => $faq->getTranslation('answer', $locale),
                ])->all(),
        ];
    }
}
