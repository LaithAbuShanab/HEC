<?php

namespace App\Domain\Site;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Guide;
use App\Models\Industry;
use App\Models\Page;
use App\Models\Product;
use App\Models\Service;
use App\Models\WorkItem;
use Illuminate\Support\Facades\Cache;

final class SiteNavigation
{
    public const CACHE_PREFIX = 'site.navigation.';

    public function forLocale(string $locale): array
    {
        return Cache::remember(self::CACHE_PREFIX.$locale, now()->addHour(), fn () => $this->build($locale));
    }

    public static function flush(): void
    {
        foreach (['ar', 'en'] as $locale) {
            Cache::forget(self::CACHE_PREFIX.$locale);
        }
    }

    private function build(string $locale): array
    {
        $counts = Product::published()
            ->selectRaw('primary_category_id, count(*) as total')
            ->groupBy('primary_category_id')
            ->pluck('total', 'primary_category_id');

        $categories = Category::published()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->get()
            ->map(fn (Category $category) => [
                'slug' => $category->slug,
                'name' => (string) $category->getTranslation('name', $locale),
                'products' => (int) ($counts[$category->id] ?? 0),
            ])
            ->values()
            ->all();

        return [
            'categories' => $categories,
            'sections' => [
                'services' => Service::published()->exists(),
                'industries' => Industry::published()->exists(),
                'guides' => Guide::published()->exists(),
                'work' => WorkItem::published()->exists(),
                'faq' => Faq::published()->exists(),
                'about' => Page::published()->where('key', 'about')->exists(),
                'privacy' => Page::published()->where('key', 'privacy')->exists(),
                'terms' => Page::published()->where('key', 'terms')->exists(),
            ],
        ];
    }
}
