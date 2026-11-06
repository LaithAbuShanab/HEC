<?php

namespace App\Filament\Admin\Resources\QuoteRequests;

use App\Domain\Quotes\Enums\QuoteStatus;
use App\Filament\Admin\Navigation\NavigationGroup;
use App\Filament\Admin\Resources\QuoteRequests\Pages\ListQuoteRequests;
use App\Filament\Admin\Resources\QuoteRequests\Pages\ViewQuoteRequest;
use App\Filament\Admin\Resources\QuoteRequests\Schemas\QuoteRequestInfolist;
use App\Filament\Admin\Resources\QuoteRequests\Tables\QuoteRequestsTable;
use App\Models\QuoteRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $recordTitleAttribute = 'reference';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return NavigationGroup::Sales;
    }

    public static function getModelLabel(): string
    {
        return __('admin.quotes.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.quotes.plural');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = QuoteRequest::where('status', QuoteStatus::New)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function getRecordTitle(?Model $record): string
    {
        return $record instanceof QuoteRequest ? (string) $record->reference : '';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['reference', 'name', 'phone', 'company'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        if (! $record instanceof QuoteRequest) {
            return [];
        }

        return array_filter([
            __('admin.quotes.fields.name') => $record->name,
            __('admin.quotes.fields.status') => $record->status->label(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuoteRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuoteRequestsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('assignee')->withCount('items');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuoteRequests::route('/'),
            'view' => ViewQuoteRequest::route('/{record}'),
        ];
    }
}
