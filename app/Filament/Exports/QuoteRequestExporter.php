<?php

namespace App\Filament\Exports;

use App\Domain\Quotes\Enums\FulfillmentMethod;
use App\Domain\Quotes\Enums\QuoteStatus;
use App\Models\QuoteRequest;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QuoteRequestExporter extends Exporter
{
    protected static ?string $model = QuoteRequest::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('reference')->label(__('admin.quotes.fields.reference')),
            ExportColumn::make('created_at')->label(__('admin.quotes.fields.created_at'))
                ->formatStateUsing(fn ($state) => $state?->format('Y-m-d H:i')),
            ExportColumn::make('status')->label(__('admin.quotes.fields.status'))
                ->formatStateUsing(fn (QuoteStatus $state) => $state->label('ar')),
            ExportColumn::make('name')->label(__('admin.quotes.fields.name')),
            ExportColumn::make('phone')->label(__('admin.quotes.fields.phone')),
            ExportColumn::make('company')->label(__('admin.quotes.fields.company')),
            ExportColumn::make('email')->label(__('admin.quotes.fields.email')),
            ExportColumn::make('city')->label(__('admin.quotes.fields.city')),
            ExportColumn::make('fulfillment')->label(__('admin.quotes.fields.fulfillment'))
                ->formatStateUsing(fn (FulfillmentMethod $state) => $state->label('ar')),
            ExportColumn::make('needed_by')->label(__('admin.quotes.fields.needed_by'))
                ->formatStateUsing(fn ($state) => $state?->format('Y-m-d')),
            ExportColumn::make('items')->label(__('admin.quotes.sections.items'))
                ->state(fn (QuoteRequest $record) => $record->items
                    ->map(fn (Model $item) => trim(implode(' ', array_filter([
                        $item->getAttribute('product_name'),
                        $item->getAttribute('thickness'),
                        '× '.$item->getAttribute('quantity'),
                        $item->getAttribute('unit'),
                    ]))))
                    ->implode(' | ')),
            ExportColumn::make('notes')->label(__('admin.quotes.fields.notes')),
            ExportColumn::make('assignee.name')->label(__('admin.quotes.fields.assignee')),
            ExportColumn::make('locale')->label(__('admin.quotes.fields.locale')),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with(['items', 'assignee']);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('admin.quotes.export_completed', ['count' => $export->successful_rows]);

        if ($failed = $export->getFailedRowsCount()) {
            $body .= ' '.__('admin.quotes.export_failed', ['count' => $failed]);
        }

        return $body;
    }
}
