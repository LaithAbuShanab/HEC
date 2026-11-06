<?php

namespace App\Filament\Admin\Resources\Products;

use App\Filament\Admin\Resources\Products\Pages\CreateProduct;
use App\Filament\Admin\Resources\Products\Pages\EditProduct;
use App\Filament\Admin\Resources\Products\Pages\ListProducts;
use App\Filament\Admin\Resources\Products\RelationManagers\MediaRelationManager;
use App\Filament\Admin\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Filament\Admin\Resources\Products\Schemas\ProductForm;
use App\Filament\Admin\Resources\Products\Tables\ProductsTable;
use App\Filament\Admin\Resources\TranslatableResource;
use App\Models\Product;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductResource extends TranslatableResource
{
    protected static ?string $model = Product::class;

    protected static string $langKey = 'products';

    protected static string $navigationGroupKey = 'catalog';

    protected static string $titleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['search_text_ar', 'search_text_en', 'slug'];
    }

    protected static function applyGlobalSearchAttributeConstraints(Builder $query, string $search): void
    {
        ProductsTable::search($query, $search);
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record instanceof Product ? (string) $record->getTranslation('name', app()->getLocale()) : '';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        if (! $record instanceof Product) {
            return [];
        }

        return array_filter([
            __('admin.products.fields.appendix_no') => $record->appendix_no,
            __('admin.common.review_status') => $record->review_status->label(),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            VariantsRelationManager::class,
            MediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['primaryCategory', 'media']);
    }
}
