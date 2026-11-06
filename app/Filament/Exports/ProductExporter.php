<?php

namespace App\Filament\Exports;

use App\Domain\Catalog\Exchange\ProductSheet;
use App\Domain\Localization\Locale;
use App\Models\Category;
use App\Models\Product;
use App\Models\SaleUnit;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        $columns = [
            ExportColumn::make('slug')->label('slug'),
            ExportColumn::make('appendix_no')->label('appendix_no'),
        ];

        foreach (array_keys(ProductSheet::TRANSLATED) as $attribute) {
            foreach (Locale::cases() as $locale) {
                $columns[] = ExportColumn::make("{$attribute}_{$locale->value}")
                    ->label("{$attribute}_{$locale->value}")
                    ->state(fn (Product $record) => $record->getTranslation($attribute, $locale->value, false));
            }
        }

        $columns[] = ExportColumn::make('category')
            ->label('category')
            ->state(fn (Product $record) => $record->primaryCategory instanceof Category ? $record->primaryCategory->slug : null);

        foreach (ProductSheet::PLAIN as $attribute) {
            $columns[] = ExportColumn::make($attribute)->label($attribute);
        }

        return [
            ...$columns,
            ExportColumn::make('sale_unit')->label('sale_unit')
                ->state(fn (Product $record) => $record->saleUnit instanceof SaleUnit ? $record->saleUnit->key : null),
            ExportColumn::make('condition')->label('condition')->state(fn (Product $record) => $record->condition->value),
            ExportColumn::make('availability')->label('availability')->state(fn (Product $record) => $record->availability->value),
            ExportColumn::make('price')->label('price'),
            ExportColumn::make('review_status')->label('review_status')->state(fn (Product $record) => $record->review_status->value),
            ExportColumn::make('missing_fields')->label('missing_fields')
                ->state(fn (Product $record) => implode(ProductSheet::LIST_SEPARATOR, $record->missing_fields ?? [])),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with(['primaryCategory', 'saleUnit']);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return __('admin.exchange.export_completed', ['count' => $export->successful_rows]);
    }
}
