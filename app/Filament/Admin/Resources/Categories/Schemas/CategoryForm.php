<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Filament\Admin\Resources\Products\Schemas\ProductForm;
use App\Filament\Admin\Support\Bilingual;
use App\Filament\Admin\Support\Options;
use App\Filament\Admin\Support\SeoFields;
use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                Bilingual::fields('name', __('admin.categories.fields.name'), fn (string $path) => TextInput::make($path)->maxLength(120), required: true),
                Bilingual::fields('description', __('admin.categories.fields.description'), fn (string $path) => Textarea::make($path)
                    ->rows(4)
                    ->maxLength(600)
                    ->helperText(__('admin.categories.fields.description_help'))),
                Grid::make(4)->schema([
                    TextInput::make('slug')
                        ->label(__('admin.common.slug'))
                        ->helperText(__('admin.common.slug_help'))
                        ->required()
                        ->maxLength(120)
                        ->regex(ProductForm::SLUG_PATTERN)
                        ->unique(ignoreRecord: true)
                        ->extraInputAttributes(['dir' => 'ltr']),
                    Select::make('parent_id')
                        ->label(__('admin.categories.fields.parent'))
                        ->options(fn (?Category $record) => array_filter(Options::categories(), fn (int $id) => $id !== $record?->id, ARRAY_FILTER_USE_KEY)),
                    Select::make('review_status')
                        ->label(__('admin.common.review_status'))
                        ->options(ReviewStatus::options('ar'))
                        ->default(ReviewStatus::Draft->value)
                        ->required(),
                    TextInput::make('sort')->label(__('admin.common.sort'))->numeric()->minValue(0)->default(0),
                ]),
                SpatieMediaLibraryFileUpload::make('cover')
                    ->label(__('admin.categories.fields.cover'))
                    ->collection('cover')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(8192)
                    ->imageEditor(),
            ]),
            SeoFields::section()->columnSpanFull(),
        ]);
    }
}
