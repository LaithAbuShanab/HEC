<?php

namespace App\Filament\Admin\Widgets;

use App\Domain\Access\Role;
use App\Domain\Reporting\QuoteMetrics;
use App\Filament\Admin\Widgets\Concerns\VisibleToRoles;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class QuotesTrendChart extends ChartWidget
{
    use VisibleToRoles;

    protected static ?int $sort = 2;

    protected ?string $maxHeight = '260px';

    protected static function roles(): array
    {
        return [Role::Sales];
    }

    public function getHeading(): string
    {
        return __('admin.dashboard.quotes.trend');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $series = app(QuoteMetrics::class)->dailySeries(30);

        return [
            'datasets' => [[
                'label' => __('admin.quotes.plural'),
                'data' => array_values($series),
                'borderColor' => '#3C6CA8',
                'backgroundColor' => 'rgba(76, 163, 214, 0.15)',
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => array_map(fn (string $day) => Carbon::parse($day)->format('m/d'), array_keys($series)),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
