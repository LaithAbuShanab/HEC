<?php

namespace App\Domain\Catalog;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Domain\Catalog\Support\SearchNormalizer;
use App\Models\Category;
use App\Models\Product;
use App\Models\SearchLog;
use App\Models\SearchSynonym;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class CatalogSearch
{
    public const MAX_QUERY_LENGTH = 100;

    public function products(string $query, int $limit = 48): Collection
    {
        $terms = $this->terms($query);

        if ($terms === []) {
            return collect();
        }

        return Product::published()
            ->with(['media', 'primaryCategory', 'variants'])
            ->whereHas('primaryCategory', fn (Builder $category) => $category->where('review_status', ReviewStatus::Published))
            ->where(function (Builder $builder) use ($terms) {
                foreach ($terms as $term) {
                    $builder->orWhere('search_text_ar', 'like', "%{$term}%")
                        ->orWhere('search_text_en', 'like', "%{$term}%");
                }
            })
            ->orderBy('sort')
            ->limit($limit)
            ->get();
    }

    public function categories(string $query): Collection
    {
        $terms = $this->terms($query);

        if ($terms === []) {
            return collect();
        }

        return Category::published()->orderBy('sort')->get()->filter(function (Category $category) use ($terms) {
            $haystack = SearchNormalizer::normalize(implode(' ', array_filter([
                $category->getTranslation('name', 'ar', false),
                $category->getTranslation('name', 'en', false),
            ])));

            foreach ($terms as $term) {
                if (str_contains($haystack, $term)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    public function terms(string $query): array
    {
        $query = mb_substr(trim($query), 0, self::MAX_QUERY_LENGTH);

        if (mb_strlen(SearchNormalizer::normalize($query)) < 2) {
            return [];
        }

        $expanded = SearchNormalizer::expand($query, SearchSynonym::groups());

        $tokens = [];
        foreach ($expanded as $term) {
            $tokens[] = $term;

            foreach (explode(' ', $term) as $token) {
                if (mb_strlen($token) >= 3) {
                    $tokens[] = $token;
                }
            }
        }

        return array_values(array_unique(array_map(fn (string $term) => addcslashes($term, '%_\\'), $tokens)));
    }

    public function log(string $query, string $locale, int $results): void
    {
        $clean = self::anonymize($query);

        if ($clean === '') {
            return;
        }

        SearchLog::create(['locale' => $locale, 'query' => $clean, 'results_count' => $results]);
    }

    public static function anonymize(string $query): string
    {
        $query = SearchNormalizer::normalize(mb_substr($query, 0, self::MAX_QUERY_LENGTH));
        $query = preg_replace('/[^\s@]+@[^\s@]+/u', '', $query) ?? '';
        $query = preg_replace('/\+?\d[\d\s\-]{6,}\d/u', '', $query) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $query) ?? '');
    }
}
