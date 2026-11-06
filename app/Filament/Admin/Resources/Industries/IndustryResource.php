<?php

namespace App\Filament\Admin\Resources\Industries;

use App\Filament\Admin\Resources\Industries\Pages\CreateIndustry;
use App\Filament\Admin\Resources\Industries\Pages\EditIndustry;
use App\Filament\Admin\Resources\Industries\Pages\ListIndustries;
use App\Filament\Admin\Resources\Industries\Schemas\IndustryForm;
use App\Filament\Admin\Resources\TranslatableResource;
use App\Filament\Admin\Support\ContentTable;
use App\Models\Industry;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IndustryResource extends TranslatableResource
{
    protected static ?string $model = Industry::class;

    protected static string $langKey = 'industries';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return IndustryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentTable::configure($table, imageCollection: 'cover', reorderable: true);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIndustries::route('/'),
            'create' => CreateIndustry::route('/create'),
            'edit' => EditIndustry::route('/{record}/edit'),
        ];
    }
}
