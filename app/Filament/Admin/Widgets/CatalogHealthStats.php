<?php

namespace App\Filament\Admin\Widgets;

use App\Domain\Access\Role;
use App\Domain\Reporting\CatalogHealth;
use App\Filament\Admin\Resources\Products\ProductResource;
use App\Filament\Admin\Widgets\Concerns\VisibleToRoles;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CatalogHealthStats extends StatsOverviewWidget
{
    use VisibleToRoles;

    protected static ?int $sort = 5;

    protected static function roles(): array
    {
        return [Role::Editor];
    }

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        $health = app(CatalogHealth::class)->summary();
        $total = __('admin.dashboard.catalog.of_total', ['total' => $health['total']]);
        $products = ProductResource::getUrl('index');

        return [
            Stat::make(__('admin.dashboard.catalog.published'), $health['published'])->description($total)->color('success')->icon('heroicon-o-check-badge'),
            Stat::make(__('admin.dashboard.catalog.pending'), $health['pending'])->color('warning')->icon('heroicon-o-clock')
                ->url($products.'?'.http_build_query(['filters' => ['review_status' => ['value' => 'pending']]])),
            Stat::make(__('admin.dashboard.catalog.draft'), $health['draft'])->color('gray')->icon('heroicon-o-pencil')
                ->url($products.'?'.http_build_query(['filters' => ['review_status' => ['value' => 'draft']]])),
            Stat::make(__('admin.dashboard.catalog.missing_main_image'), $health['missing_main_image'])->color($health['missing_main_image'] > 0 ? 'danger' : 'success')->icon('heroicon-o-photo')
                ->url($products.'?'.http_build_query(['filters' => ['missing_main_image' => ['value' => '1']]])),
            Stat::make(__('admin.dashboard.catalog.with_missing_fields'), $health['with_missing_fields'])->color($health['with_missing_fields'] > 0 ? 'warning' : 'success')->icon('heroicon-o-clipboard-document-list')
                ->url($products.'?'.http_build_query(['filters' => ['has_missing_fields' => ['value' => '1']]])),
        ];
    }
}
