<?php

namespace App\Filament\Admin\Resources\SpecAttributes;

use App\Filament\Admin\Resources\SpecAttributes\Pages\ManageSpecAttributes;
use App\Filament\Admin\Resources\TranslatableResource;
use App\Filament\Admin\Support\Bilingual;
use App\Filament\Admin\Support\Columns;
use App\Filament\Admin\Support\GuardedDelete;
use App\Filament\Admin\Support\TranslatableActions;
use App\Models\SpecAttribute;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SpecAttributeResource extends TranslatableResource
{
    protected static ?string $model = SpecAttribute::class;

    protected static string $langKey = 'spec_attributes';

    protected static string $navigationGroupKey = 'catalog';

    protected static string $titleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Bilingual::fields('name', __('admin.spec_attributes.fields.name'), fn (string $path) => TextInput::make($path)->maxLength(80), required: true),
            Grid::make(3)->columnSpanFull()->schema([
                TextInput::make('key')
                    ->label(__('admin.spec_attributes.fields.key'))
                    ->required()
                    ->regex('/^[a-z0-9_]+$/')
                    ->maxLength(40)
                    ->unique(ignoreRecord: true)
                    ->extraInputAttributes(['dir' => 'ltr']),
                TextInput::make('unit')
                    ->label(__('admin.spec_attributes.fields.unit'))
                    ->helperText(__('admin.spec_attributes.fields.unit_help'))
                    ->maxLength(16)
                    ->extraInputAttributes(['dir' => 'ltr']),
                Select::make('type')
                    ->label(__('admin.spec_attributes.fields.type'))
                    ->options(__('admin.spec_attributes.types'))
                    ->default('text')
                    ->required(),
            ]),
            Toggle::make('is_filterable')
                ->label(__('admin.spec_attributes.fields.is_filterable'))
                ->helperText(__('admin.spec_attributes.fields.is_filterable_help')),
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
                    ->label(__('admin.spec_attributes.fields.name'))
                    ->state(fn (SpecAttribute $record) => $record->getTranslation('name', app()->getLocale()))
                    ->description(fn (SpecAttribute $record) => $record->getTranslation('name', Columns::otherLocale(), false)),
                TextColumn::make('key')->label(__('admin.spec_attributes.fields.key'))->badge()->color('gray'),
                TextColumn::make('unit')->label(__('admin.spec_attributes.fields.unit'))->placeholder('—'),
                TextColumn::make('type')
                    ->label(__('admin.spec_attributes.fields.type'))
                    ->formatStateUsing(fn (string $state) => __("admin.spec_attributes.types.{$state}")),
                IconColumn::make('is_filterable')->label(__('admin.spec_attributes.fields.is_filterable'))->boolean(),
            ])
            ->recordActions([
                TranslatableActions::edit(),
                GuardedDelete::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSpecAttributes::route('/'),
        ];
    }
}
