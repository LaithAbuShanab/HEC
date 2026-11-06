<?php

namespace App\Domain\Reporting;

use App\Models\SearchLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class SearchInsights
{
    public function top(int $days = 30, int $limit = 10): Collection
    {
        return $this->grouped($days)->limit($limit)->get();
    }

    public function withoutResults(int $days = 30, int $limit = 10): Collection
    {
        return $this->grouped($days)->havingRaw('max(results_count) = 0')->limit($limit)->get();
    }

    public function grouped(int $days = 30): Builder
    {
        return SearchLog::query()
            ->selectRaw('min(id) as id, query, count(*) as searches, max(results_count) as best_results')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('query')
            ->orderByDesc('searches')
            ->orderBy('query');
    }
}
