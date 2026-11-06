<?php

namespace App\Filament\Admin\Widgets;

use App\Domain\Access\Role;
use App\Domain\Quotes\Enums\QuoteStatus;
use App\Filament\Admin\Resources\QuoteRequests\QuoteRequestResource;
use App\Filament\Admin\Resources\QuoteRequests\Schemas\QuoteRequestInfolist;
use App\Filament\Admin\Widgets\Concerns\VisibleToRoles;
use App\Models\QuoteRequest;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestQuotesTable extends TableWidget
{
    use VisibleToRoles;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static function roles(): array
    {
        return [Role::Sales];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.dashboard.quotes.latest'))
            ->query(QuoteRequest::query()->where('status', '!=', QuoteStatus::Spam)->withCount('items')->latest()->limit(8))
            ->paginated(false)
            ->recordUrl(fn (QuoteRequest $record) => QuoteRequestResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('reference')->label(__('admin.quotes.fields.reference'))->weight('bold'),
                TextColumn::make('name')->label(__('admin.quotes.fields.name'))->description(fn (QuoteRequest $record) => $record->company),
                TextColumn::make('city')->label(__('admin.quotes.fields.city')),
                TextColumn::make('items_count')->label(__('admin.quotes.fields.items_count'))->badge()->color('gray'),
                TextColumn::make('status')
                    ->label(__('admin.quotes.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (QuoteStatus $state) => $state->label('ar'))
                    ->color(fn (QuoteStatus $state) => QuoteRequestInfolist::statusColor($state)),
                TextColumn::make('created_at')->label(__('admin.quotes.fields.created_at'))->since(),
            ]);
    }
}
