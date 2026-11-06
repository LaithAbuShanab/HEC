<?php

namespace App\Filament\Admin\Widgets;

use App\Domain\Access\Role;
use App\Domain\Reporting\SearchInsights;
use App\Filament\Admin\Widgets\Concerns\VisibleToRoles;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ZeroResultSearchesTable extends TableWidget
{
    use VisibleToRoles;

    protected static ?int $sort = 8;

    protected static function roles(): array
    {
        return [Role::Editor];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.dashboard.search.no_results'))
            ->description(__('admin.dashboard.search.no_results_hint'))
            ->query(app(SearchInsights::class)->grouped(30)->havingRaw('max(results_count) = 0')->limit(10))
            ->paginated(false)
            ->columns([
                TextColumn::make('query')->label(__('admin.dashboard.search.query')),
                TextColumn::make('searches')->label(__('admin.dashboard.search.searches'))->badge()->color('danger'),
            ]);
    }
}
