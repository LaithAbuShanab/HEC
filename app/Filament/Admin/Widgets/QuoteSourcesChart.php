<?php

namespace App\Filament\Admin\Widgets;

use App\Domain\Access\Role;
use App\Domain\Reporting\QuoteMetrics;
use App\Filament\Admin\Widgets\Concerns\VisibleToRoles;
use Filament\Widgets\ChartWidget;

class QuoteSourcesChart extends ChartWidget
{
    use VisibleToRoles;

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '260px';

    private const PALETTE = ['#3C6CA8', '#E4B42A', '#4CA3D6', '#0E1B30', '#94A3B8', '#1F8A70'];

    protected static function roles(): array
    {
        return [Role::Sales];
    }

    public function getHeading(): string
    {
        return __('admin.dashboard.quotes.sources');
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $sources = app(QuoteMetrics::class)->sources(90);
        $labels = array_map(fn (string $source) => $source === 'direct' ? __('admin.dashboard.quotes.direct') : $source, array_keys($sources));

        return [
            'datasets' => [[
                'data' => array_values($sources),
                'backgroundColor' => array_slice(array_pad(self::PALETTE, max(count($sources), 1), '#CBD5E1'), 0, max(count($sources), 1)),
            ]],
            'labels' => $labels,
        ];
    }
}
