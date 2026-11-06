<?php

namespace App\Domain\Reporting;

use App\Models\SearchLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class SearchInsights
{
    public function top(int $days = 30, int $limit = 10): Collection
    {
        return $this->ranked($days)->limit($limit)->get();
    }

    public function withoutResults(int $days = 30, int $limit = 10): Collection
    {
        return $this->ranked($days, onlyWithoutResults: true)->limit($limit)->get();
    }

    public function ranked(int $days = 30, bool $onlyWithoutResults = false): Builder
    {
        $grouped = SearchLog::query()
            ->toBase()
            ->selectRaw('min(id) as id, query, count(*) as searches, max(results_count) as best_results')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('query')
            ->when($onlyWithoutResults, fn ($query) => $query->havingRaw('max(results_count) = 0'));

        return SearchLog::query()
            ->fromSub($grouped, 'search_logs')
            ->orderByDesc('searches')
            ->orderBy('query');
    }
}
