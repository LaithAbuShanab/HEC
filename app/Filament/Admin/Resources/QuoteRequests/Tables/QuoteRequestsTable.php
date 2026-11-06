<?php

namespace App\Filament\Admin\Resources\QuoteRequests\Tables;

use App\Domain\Quotes\Enums\NotificationStatus;
use App\Domain\Quotes\Enums\QuoteStatus;
use App\Filament\Admin\Resources\QuoteRequests\Actions\QuoteActions;
use App\Filament\Admin\Resources\QuoteRequests\Schemas\QuoteRequestInfolist;
use App\Filament\Admin\Support\Display;
use App\Filament\Exports\QuoteRequestExporter;
use App\Models\QuoteRequest;
use Filament\Actions\ActionGroup;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuoteRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label(__('admin.quotes.fields.reference'))
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('name')
                    ->label(__('admin.quotes.fields.name'))
                    ->description(fn (QuoteRequest $record) => $record->company)
                    ->searchable(['name', 'company']),
                TextColumn::make('phone')
                    ->label(__('admin.quotes.fields.phone'))
                    ->searchable()
                    ->formatStateUsing(fn (?string $state) => Display::ltr($state)),
                TextColumn::make('city')->label(__('admin.quotes.fields.city'))->toggleable(),
                TextColumn::make('items_count')->label(__('admin.quotes.fields.items_count'))->badge()->color('gray'),
                TextColumn::make('status')
                    ->label(__('admin.quotes.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (QuoteStatus $state) => $state->label('ar'))
                    ->color(fn (QuoteStatus $state) => QuoteRequestInfolist::statusColor($state)),
                IconColumn::make('notification_status')
                    ->label(__('admin.quotes.fields.notification_status'))
                    ->icon(fn (NotificationStatus $state) => match ($state) {
                        NotificationStatus::Pending => 'heroicon-o-clock',
                        NotificationStatus::Sent => 'heroicon-o-check-circle',
                        NotificationStatus::Failed => 'heroicon-o-exclamation-triangle',
                    })
                    ->color(fn (NotificationStatus $state) => match ($state) {
                        NotificationStatus::Pending => 'warning',
                        NotificationStatus::Sent => 'success',
                        NotificationStatus::Failed => 'danger',
                    })
                    ->tooltip(fn (NotificationStatus $state) => $state->label('ar')),
                TextColumn::make('assignee.name')->label(__('admin.quotes.fields.assignee'))->placeholder('—')->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('admin.quotes.fields.created_at'))
                    ->since()
                    ->dateTimeTooltip('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.quotes.fields.status'))
                    ->options(QuoteStatus::options('ar'))
                    ->multiple(),
                Filter::make('mine')
                    ->label(__('admin.quotes.filters.mine'))
                    ->query(fn (Builder $query) => $query->where('assigned_to', auth()->id()))
                    ->toggle(),
                Filter::make('unassigned')
                    ->label(__('admin.quotes.filters.unassigned'))
                    ->query(fn (Builder $query) => $query->whereNull('assigned_to'))
                    ->toggle(),
                Filter::make('notification_failed')
                    ->label(__('admin.quotes.filters.notification_failed'))
                    ->query(fn (Builder $query) => $query->where('notification_status', NotificationStatus::Failed))
                    ->toggle(),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->label(__('admin.quotes.filters.created_from')),
                        DatePicker::make('until')->label(__('admin.quotes.filters.created_until')),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $inner, $date) => $inner->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $inner, $date) => $inner->whereDate('created_at', '<=', $date))),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    QuoteActions::changeStatus(),
                    QuoteActions::assignToMe(),
                    QuoteActions::assign(),
                    QuoteActions::call(),
                    QuoteActions::whatsapp(),
                    QuoteActions::markSpam(),
                ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label(__('admin.quotes.actions.export'))
                    ->exporter(QuoteRequestExporter::class)
                    ->authorize('export'),
            ])
            ->toolbarActions([
                ExportBulkAction::make()
                    ->label(__('admin.quotes.actions.export'))
                    ->exporter(QuoteRequestExporter::class)
                    ->authorize('export'),
            ]);
    }
}
