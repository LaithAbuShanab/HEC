<?php

namespace App\Filament\Admin\Widgets;

use App\Domain\Access\Role;
use App\Domain\Reporting\SearchInsights;
use App\Filament\Admin\Widgets\Concerns\VisibleToRoles;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class SearchInsightsTable extends TableWidget
{
    use VisibleToRoles;

    protected static ?int $sort = 7;

    protected static function roles(): array
    {
        return [Role::Editor];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.dashboard.search.top'))
            ->query(app(SearchInsights::class)->grouped(30)->limit(10))
            ->paginated(false)
            ->columns([
                TextColumn::make('query')->label(__('admin.dashboard.search.query')),
                TextColumn::make('searches')->label(__('admin.dashboard.search.searches'))->badge(),
                TextColumn::make('best_results')
                    ->label(__('admin.dashboard.search.results'))
                    ->badge()
                    ->color(fn (int $state) => $state === 0 ? 'danger' : 'success'),
            ]);
    }
}
