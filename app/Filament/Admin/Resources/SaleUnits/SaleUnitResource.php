<?php

namespace App\Filament\Admin\Resources\SaleUnits;

use App\Filament\Admin\Resources\SaleUnits\Pages\ManageSaleUnits;
use App\Filament\Admin\Resources\TranslatableResource;
use App\Filament\Admin\Support\Bilingual;
use App\Filament\Admin\Support\Columns;
use App\Filament\Admin\Support\GuardedDelete;
use App\Filament\Admin\Support\TranslatableActions;
use App\Models\SaleUnit;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SaleUnitResource extends TranslatableResource
{
    protected static ?string $model = SaleUnit::class;

    protected static string $langKey = 'sale_units';

    protected static string $navigationGroupKey = 'catalog';

    protected static string $titleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Bilingual::fields('name', __('admin.sale_units.fields.name'), fn (string $path) => TextInput::make($path)->maxLength(40), required: true),
            TextInput::make('key')
                ->label(__('admin.sale_units.fields.key'))
                ->helperText(__('admin.sale_units.fields.key_help'))
                ->required()
                ->regex('/^[a-z_]+$/')
                ->maxLength(30)
                ->unique(ignoreRecord: true)
                ->extraInputAttributes(['dir' => 'ltr']),
            TextInput::make('sort')->label(__('admin.common.sort'))->numeric()->minValue(0)->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.sale_units.fields.name'))
                    ->state(fn (SaleUnit $record) => $record->getTranslation('name', app()->getLocale()))
                    ->description(fn (SaleUnit $record) => $record->getTranslation('name', Columns::otherLocale(), false)),
                TextColumn::make('key')->label(__('admin.sale_units.fields.key'))->badge()->color('gray'),
            ])
            ->recordActions([
                TranslatableActions::edit(),
                GuardedDelete::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSaleUnits::route('/'),
        ];
    }
}
