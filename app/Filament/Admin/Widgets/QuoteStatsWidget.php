<?php

namespace App\Filament\Admin\Widgets;

use App\Domain\Access\Role;
use App\Domain\Reporting\QuoteMetrics;
use App\Filament\Admin\Resources\QuoteRequests\QuoteRequestResource;
use App\Filament\Admin\Widgets\Concerns\VisibleToRoles;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuoteStatsWidget extends StatsOverviewWidget
{
    use VisibleToRoles;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected static function roles(): array
    {
        return [Role::Sales];
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $metrics = app(QuoteMetrics::class)->summary($user instanceof User ? $user->id : null);
        $index = QuoteRequestResource::getUrl('index');

        return [
            Stat::make(__('admin.dashboard.quotes.new'), $metrics['new'])
                ->description(__('admin.dashboard.quotes.new_hint'))
                ->icon('heroicon-o-inbox-arrow-down')
                ->color($metrics['new'] > 0 ? 'warning' : 'gray')
                ->url($index),
            Stat::make(__('admin.dashboard.quotes.last_7_days'), $metrics['last_7_days'])
                ->icon('heroicon-o-calendar-days')
                ->color('info'),
            Stat::make(__('admin.dashboard.quotes.assigned_open'), $metrics['assigned_open'])
                ->icon('heroicon-o-user')
                ->color('primary'),
            Stat::make(__('admin.dashboard.quotes.notification_failed'), $metrics['notification_failed'])
                ->description(__('admin.dashboard.quotes.notification_failed_hint'))
                ->icon('heroicon-o-exclamation-triangle')
                ->color($metrics['notification_failed'] > 0 ? 'danger' : 'success'),
        ];
    }
}
