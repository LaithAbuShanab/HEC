<?php

namespace App\Filament\Imports;

use App\Domain\Catalog\Enums\Availability;
use App\Domain\Catalog\Enums\ProductCondition;
use App\Domain\Catalog\Enums\ReviewStatus;
use App\Domain\Catalog\Exchange\ProductSheet;
use App\Domain\Localization\Locale;
use App\Filament\Admin\Support\Fields;
use App\Models\Category;
use App\Models\Product;
use App\Models\SaleUnit;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        $columns = [
            self::column('slug')
                ->requiredMapping()
                ->rules(['required', 'max:120', 'regex:'.Fields::SLUG_PATTERN]),
        ];

        foreach (array_keys(ProductSheet::TRANSLATED) as $attribute) {
            foreach (Locale::cases() as $locale) {
                $columns[] = self::column("{$attribute}_{$locale->value}")
                    ->rules(['nullable', 'string', 'max:'.($attribute === 'name' ? 160 : 600)])
                    ->fillRecordUsing(fn (Product $record, ?string $state) => $record->setTranslation($attribute, $locale->value, trim((string) $state)));
            }
        }

        $columns[] = self::column('category')
            ->rules(['nullable', Rule::exists('categories', 'slug')])
            ->fillRecordUsing(fn (Product $record, ?string $state) => $record->primary_category_id = Category::where('slug', $state)->value('id'));

        foreach (ProductSheet::PLAIN as $attribute) {
            $columns[] = self::column($attribute)->rules(['nullable', 'string', 'max:80']);
        }

        return [
            ...$columns,
            self::column('sale_unit')
                ->rules(['nullable', Rule::exists('sale_units', 'key')])
                ->fillRecordUsing(fn (Product $record, ?string $state) => $record->sale_unit_id = SaleUnit::where('key', $state)->value('id')),
            self::column('condition')->rules(['nullable', Rule::enum(ProductCondition::class)]),
            self::column('availability')->rules(['nullable', Rule::enum(Availability::class)]),
            self::column('price')->numeric(decimalPlaces: 3)->rules(['nullable', 'numeric', 'min:0.001']),
            self::column('review_status')->rules(['nullable', Rule::enum(ReviewStatus::class)]),
            self::column('missing_fields')
                ->castStateUsing(fn (?string $state) => ProductSheet::splitList($state))
                ->fillRecordUsing(fn (Product $record, array $state) => $record->missing_fields = $state),
        ];
    }

    private static function column(string $name): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__("admin.exchange.columns.{$name}"))
            ->guess([$name, __("admin.exchange.columns.{$name}")])
            ->ignoreBlankState();
    }

    public function resolveRecord(): Product
    {
        return Product::withTrashed()->firstOrNew(['slug' => trim((string) ($this->data['slug'] ?? ''))]);
    }

    protected function beforeSave(): void
    {
        $product = $this->record;

        if (! $product instanceof Product) {
            return;
        }

        if (! $product->exists) {
            $hasNames = filled($product->getTranslation('name', 'ar', false)) && filled($product->getTranslation('name', 'en', false));

            if (! $hasNames || blank($product->primary_category_id)) {
                throw new RowImportFailedException(__('admin.exchange.new_requires_names'));
            }
        }

        if ($product->isDirty('review_status') && $product->review_status === ReviewStatus::Published) {
            $blockers = $product->exists ? $product->publicationBlockers() : ['main_image'];

            if ($blockers !== []) {
                throw new RowImportFailedException(__('admin.exchange.publication_blocked', [
                    'blockers' => collect($blockers)->map(fn (string $key) => __("admin.publication.blockers.{$key}"))->implode('، '),
                ]));
            }
        }
    }

    protected function afterSave(): void
    {
        $product = $this->record;

        if ($product instanceof Product && filled($product->primary_category_id)) {
            $product->categories()->syncWithoutDetaching([$product->primary_category_id]);
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('admin.exchange.import_completed', ['count' => $import->successful_rows]);

        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' '.__('admin.exchange.import_failed', ['count' => $failed]);
        }

        return $body;
    }
}
