<?php

namespace App\Filament\Admin\Resources\Guides;

use App\Filament\Admin\Resources\Guides\Pages\CreateGuide;
use App\Filament\Admin\Resources\Guides\Pages\EditGuide;
use App\Filament\Admin\Resources\Guides\Pages\ListGuides;
use App\Filament\Admin\Resources\Guides\Schemas\GuideForm;
use App\Filament\Admin\Resources\TranslatableResource;
use App\Filament\Admin\Support\ContentTable;
use App\Models\Guide;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GuideResource extends TranslatableResource
{
    protected static ?string $model = Guide::class;

    protected static string $langKey = 'guides';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return GuideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentTable::configure($table, imageCollection: 'cover', reorderable: false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuides::route('/'),
            'create' => CreateGuide::route('/create'),
            'edit' => EditGuide::route('/{record}/edit'),
        ];
    }
}
