<?php

namespace App\Domain\Reporting;

use App\Domain\Quotes\Enums\NotificationStatus;
use App\Domain\Quotes\Enums\QuoteStatus;
use App\Models\QuoteRequest;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;

final class QuoteMetrics
{
    public function summary(?int $userId = null): array
    {
        return [
            'new' => QuoteRequest::where('status', QuoteStatus::New)->count(),
            'last_7_days' => $this->genuine()->where('created_at', '>=', now()->subDays(7))->count(),
            'assigned_open' => $userId === null ? 0 : QuoteRequest::query()
                ->where('assigned_to', $userId)
                ->whereNotIn('status', [QuoteStatus::Closed, QuoteStatus::Spam])
                ->count(),
            'notification_failed' => QuoteRequest::where('notification_status', NotificationStatus::Failed)->count(),
        ];
    }

    public function dailySeries(int $days = 30): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $counts = $this->genuine()
            ->where('created_at', '>=', $start)
            ->selectRaw('date(created_at) as day, count(*) as total')
            ->groupByRaw('date(created_at)')
            ->pluck('total', 'day');

        $series = [];
        foreach (CarbonPeriod::create($start, now()->startOfDay()) as $day) {
            $series[$day->toDateString()] = (int) ($counts[$day->toDateString()] ?? 0);
        }

        return $series;
    }

    public function sources(int $days = 90): array
    {
        $sources = $this->genuine()
            ->where('created_at', '>=', now()->subDays($days))
            ->pluck('source')
            ->countBy(fn (mixed $source) => is_array($source) && filled($source['utm_source'] ?? null) ? (string) $source['utm_source'] : 'direct')
            ->all();

        arsort($sources);

        return $sources;
    }

    private function genuine(): Builder
    {
        return QuoteRequest::query()->where('status', '!=', QuoteStatus::Spam);
    }
}
