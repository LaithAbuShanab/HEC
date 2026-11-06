<?php

namespace App\Filament\Admin\Resources\Products\RelationManagers;

use App\Domain\Catalog\Enums\Availability;
use App\Filament\Admin\Support\Bilingual;
use App\Filament\Admin\Support\Options;
use App\Models\ProductVariant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.variants.plural');
    }

    protected static function getModelLabel(): ?string
    {
        return __('admin.variants.label');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('admin.variants.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Bilingual::fields('label', __('admin.variants.fields.label'), fn (string $path) => TextInput::make($path)->maxLength(160), required: true),
            Grid::make(3)->columnSpanFull()->schema([
                TextInput::make('sku')->label(__('admin.products.fields.sku'))->maxLength(64)->unique(ignoreRecord: true),
                TextInput::make('standard')->label(__('admin.products.fields.standard'))->maxLength(80),
                TextInput::make('nominal_size')
                    ->label(__('admin.variants.fields.nominal_size'))
                    ->helperText(__('admin.variants.fields.nominal_size_help'))
                    ->maxLength(40),
            ]),
            Grid::make(4)->columnSpanFull()->schema([
                TextInput::make('thickness_mm')->label(__('admin.variants.fields.thickness_mm'))->numeric()->minValue(0),
                TextInput::make('width_mm')->label(__('admin.variants.fields.width_mm'))->numeric()->minValue(0),
                TextInput::make('length_mm')->label(__('admin.variants.fields.length_mm'))->numeric()->minValue(0),
                TextInput::make('diameter_mm')->label(__('admin.variants.fields.diameter_mm'))->numeric()->minValue(0),
            ]),
            Grid::make(3)->columnSpanFull()->schema([
                Select::make('sale_unit_id')->label(__('admin.products.fields.sale_unit'))->options(Options::saleUnits()),
                Select::make('availability')
                    ->label(__('admin.products.fields.availability'))
                    ->options(Availability::options('ar'))
                    ->placeholder(__('admin.variants.fields.inherit_availability')),
                TextInput::make('sort')->label(__('admin.common.sort'))->numeric()->minValue(0)->default(0),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('label')
                    ->label(__('admin.variants.fields.label'))
                    ->state(fn (ProductVariant $record) => $record->labelFor('ar')),
                TextColumn::make('standard')->label(__('admin.products.fields.standard'))->placeholder('—'),
                TextColumn::make('thickness_mm')->label(__('admin.variants.fields.thickness_mm'))->placeholder('—'),
                TextColumn::make('width_mm')->label(__('admin.variants.fields.width_mm'))->placeholder('—'),
                TextColumn::make('length_mm')->label(__('admin.variants.fields.length_mm'))->placeholder('—'),
                TextColumn::make('nominal_size')->label(__('admin.variants.fields.nominal_size'))->placeholder('—'),
                TextColumn::make('availability')
                    ->label(__('admin.products.fields.availability'))
                    ->formatStateUsing(fn (?Availability $state) => $state?->label('ar'))
                    ->placeholder(__('admin.variants.fields.inherit_availability')),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
