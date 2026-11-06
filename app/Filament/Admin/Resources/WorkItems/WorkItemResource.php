<?php

namespace App\Filament\Admin\Resources\WorkItems;

use App\Filament\Admin\Resources\TranslatableResource;
use App\Filament\Admin\Resources\WorkItems\Pages\CreateWorkItem;
use App\Filament\Admin\Resources\WorkItems\Pages\EditWorkItem;
use App\Filament\Admin\Resources\WorkItems\Pages\ListWorkItems;
use App\Filament\Admin\Resources\WorkItems\Schemas\WorkItemForm;
use App\Filament\Admin\Support\ContentTable;
use App\Models\WorkItem;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkItemResource extends TranslatableResource
{
    protected static ?string $model = WorkItem::class;

    protected static string $langKey = 'work_items';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return WorkItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentTable::configure($table, imageCollection: 'gallery', reorderable: true);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkItems::route('/'),
            'create' => CreateWorkItem::route('/create'),
            'edit' => EditWorkItem::route('/{record}/edit'),
        ];
    }
}
