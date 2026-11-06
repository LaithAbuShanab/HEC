<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use App\Domain\Catalog\Enums\Availability;
use App\Domain\Catalog\Enums\ProductCondition;
use App\Filament\Admin\Support\Bilingual;
use App\Filament\Admin\Support\Display;
use App\Filament\Admin\Support\Fields;
use App\Filament\Admin\Support\Options;
use App\Filament\Admin\Support\SeoFields;
use App\Models\HistoricalSpec;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs([
                    self::generalTab(),
                    self::contentTab(),
                    self::specsTab(),
                    self::availabilityTab(),
                    self::mediaTab(),
                    self::reviewTab(),
                ]),
            SeoFields::section()->columnSpanFull(),
        ]);
    }

    private static function generalTab(): Tab
    {
        return Tab::make(__('admin.products.tabs.general'))->schema([
            Bilingual::fields('name', __('admin.products.fields.name'), fn (string $path) => TextInput::make($path)->maxLength(160), required: true),
            Grid::make(3)->schema([
                Fields::slug(),
                TextInput::make('sku')
                    ->label(__('admin.products.fields.sku'))
                    ->helperText(__('admin.products.fields.sku_help'))
                    ->maxLength(64)
                    ->unique(ignoreRecord: true)
                    ->extraInputAttributes(['dir' => 'ltr']),
                TextInput::make('appendix_no')
                    ->label(__('admin.products.fields.appendix_no'))
                    ->disabled()
                    ->dehydrated(false),
            ]),
            Grid::make(2)->schema([
                Select::make('primary_category_id')
                    ->label(__('admin.products.fields.primary_category'))
                    ->options(Options::categories())
                    ->required()
                    ->searchable(),
                Select::make('categories')
                    ->label(__('admin.products.fields.categories'))
                    ->relationship('categories', 'slug')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'ar'))
                    ->multiple()
                    ->preload(),
            ]),
            Grid::make(2)->schema([
                Toggle::make('is_featured')->label(__('admin.products.fields.is_featured')),
                Fields::sort(),
            ]),
        ]);
    }

    private static function contentTab(): Tab
    {
        return Tab::make(__('admin.products.tabs.content'))->schema([
            Bilingual::fields('short_description', __('admin.products.fields.short_description'), fn (string $path) => Textarea::make($path)->rows(3)->maxLength(300)),
            Bilingual::fields('description', __('admin.products.fields.description'), fn (string $path) => RichEditor::make($path)
                ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList'], ['table'], ['undo', 'redo']]), columns: 1),
            Bilingual::fields('uses', __('admin.products.fields.uses'), fn (string $path) => Textarea::make($path)->rows(3)),
            Select::make('related')
                ->label(__('admin.products.fields.related'))
                ->relationship('related', 'slug')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'ar'))
                ->multiple()
                ->searchable()
                ->preload(),
        ]);
    }

    private static function specsTab(): Tab
    {
        return Tab::make(__('admin.products.tabs.specs'))->schema([
            Bilingual::fields('material', __('admin.products.fields.material'), fn (string $path) => TextInput::make($path)->maxLength(120)),
            Bilingual::fields('origin', __('admin.products.fields.origin'), fn (string $path) => TextInput::make($path)->maxLength(80)->helperText(__('admin.products.fields.origin_help'))),
            Grid::make(4)->schema([
                TextInput::make('grade')->label(__('admin.products.fields.grade'))->maxLength(80),
                TextInput::make('standard')->label(__('admin.products.fields.standard'))->helperText(__('admin.products.fields.standard_help'))->maxLength(80),
                TextInput::make('brand')->label(__('admin.products.fields.brand'))->maxLength(80),
                TextInput::make('model')->label(__('admin.products.fields.model'))->maxLength(80),
            ]),
            Grid::make(2)->schema([
                Select::make('sale_unit_id')->label(__('admin.products.fields.sale_unit'))->options(Options::saleUnits()),
                Select::make('condition')
                    ->label(__('admin.products.fields.condition'))
                    ->options(ProductCondition::options('ar'))
                    ->default(ProductCondition::Unconfirmed->value)
                    ->required(),
            ]),
            Repeater::make('specValues')
                ->label(__('admin.products.fields.spec_values'))
                ->relationship()
                ->schema([
                    Select::make('spec_attribute_id')
                        ->label(__('admin.products.fields.spec_attribute'))
                        ->options(Options::specAttributes())
                        ->required()
                        ->distinct(),
                    TextInput::make('value.ar')->label(__('admin.products.fields.value').' ('.__('admin.locales.ar').')'),
                    TextInput::make('value.en')->label(__('admin.products.fields.value').' ('.__('admin.locales.en').')')->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('value_numeric')->label(__('admin.products.fields.value_numeric'))->numeric(),
                ])
                ->columns(4)
                ->defaultItems(0)
                ->collapsible(),
        ]);
    }

    private static function availabilityTab(): Tab
    {
        return Tab::make(__('admin.products.tabs.availability'))->schema([
            Grid::make(2)->schema([
                Select::make('availability')
                    ->label(__('admin.products.fields.availability'))
                    ->options(Availability::options('ar'))
                    ->default(Availability::NeedsConfirmation->value)
                    ->required(),
                TextEntry::make('availability_updated_at')
                    ->label(__('admin.products.fields.availability_updated_at'))
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('—'),
            ]),
            Grid::make(2)->schema([
                TextInput::make('price')
                    ->label(__('admin.products.fields.price'))
                    ->helperText(__('admin.products.fields.price_help'))
                    ->numeric()
                    ->minValue(0.001),
                TextInput::make('price_currency')->label(__('admin.products.fields.price_currency'))->default('JOD')->maxLength(3)->required(),
            ]),
        ]);
    }

    private static function mediaTab(): Tab
    {
        $images = ['image/jpeg', 'image/png', 'image/webp'];

        return Tab::make(__('admin.products.tabs.media'))->schema([
            SpatieMediaLibraryFileUpload::make('main')
                ->label(__('admin.products.fields.main_image'))
                ->collection('main')
                ->image()
                ->acceptedFileTypes($images)
                ->maxSize(8192)
                ->imageEditor(),
            SpatieMediaLibraryFileUpload::make('gallery')
                ->label(__('admin.products.fields.gallery'))
                ->collection('gallery')
                ->image()
                ->multiple()
                ->reorderable()
                ->acceptedFileTypes($images)
                ->maxSize(8192)
                ->maxFiles(12),
            SpatieMediaLibraryFileUpload::make('documents')
                ->label(__('admin.products.fields.documents'))
                ->collection('documents')
                ->multiple()
                ->acceptedFileTypes(['application/pdf'])
                ->maxSize(20480)
                ->downloadable(),
        ]);
    }

    private static function reviewTab(): Tab
    {
        return Tab::make(__('admin.products.tabs.review'))->schema([
            Fields::reviewStatus(),
            Select::make('missing_fields')
                ->label(__('admin.products.fields.missing_fields'))
                ->helperText(__('admin.products.fields.missing_fields_help'))
                ->multiple()
                ->searchable()
                ->options(fn (?Product $record) => Display::knownMissingFields() + collect($record instanceof Product ? (array) $record->missing_fields : [])->mapWithKeys(fn (string $key) => [$key => Display::missingField($key)])->all()),
            Grid::make(2)->schema([
                Textarea::make('source_reference')->label(__('admin.products.fields.source_reference'))->rows(2),
                DatePicker::make('source_reviewed_at')->label(__('admin.products.fields.source_reviewed_at')),
            ]),
            Section::make(__('admin.products.fields.historical_specs'))
                ->visible(fn (?Product $record) => $record?->historicalSpecs()->exists() ?? false)
                ->schema([
                    TextEntry::make('historical_specs_list')
                        ->hiddenLabel()
                        ->state(fn (?Product $record) => $record === null ? [] : HistoricalSpec::where('product_id', $record->id)->get()
                            ->map(fn (HistoricalSpec $spec) => $spec->getTranslation('note', 'ar'))->all())
                        ->listWithLineBreaks()
                        ->bulleted(),
                ]),
        ]);
    }
}
