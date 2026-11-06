<?php

namespace App\Filament\Admin\Support;

use App\Domain\Catalog\Enums\ReviewStatus;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;

final class Fields
{
    public const SLUG_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public static function slug(): TextInput
    {
        return TextInput::make('slug')
            ->label(__('admin.common.slug'))
            ->helperText(__('admin.common.slug_help'))
            ->required()
            ->maxLength(120)
            ->regex(self::SLUG_PATTERN)
            ->unique(ignoreRecord: true)
            ->extraInputAttributes(['dir' => 'ltr']);
    }

    public static function reviewStatus(): Select
    {
        return Select::make('review_status')
            ->label(__('admin.common.review_status'))
            ->options(ReviewStatus::options())
            ->default(ReviewStatus::Draft->value)
            ->required();
    }

    public static function sort(): TextInput
    {
        return TextInput::make('sort')->label(__('admin.common.sort'))->numeric()->minValue(0)->default(0);
    }

    public static function title(string $name = 'title', ?string $label = null, int $max = 160): Grid
    {
        return Bilingual::fields($name, $label ?? __("admin.content.fields.{$name}"), fn (string $path) => TextInput::make($path)->maxLength($max), required: true, fillsSlug: true);
    }

    public static function text(string $name, int $rows = 3, int $max = 600, ?string $label = null): Grid
    {
        return Bilingual::fields($name, $label ?? __("admin.content.fields.{$name}"), fn (string $path) => Textarea::make($path)->rows($rows)->maxLength($max));
    }

    public static function richText(string $name, ?string $label = null): Grid
    {
        return Bilingual::fields(
            $name,
            $label ?? __("admin.content.fields.{$name}"),
            fn (string $path) => RichEditor::make($path)->toolbarButtons([
                ['bold', 'italic', 'link'],
                ['h2', 'h3'],
                ['bulletList', 'orderedList', 'blockquote'],
                ['table'],
                ['undo', 'redo'],
            ]),
            columns: 1,
        );
    }

    public static function relatedProducts(): Select
    {
        return Select::make('products')
            ->label(__('admin.content.fields.products'))
            ->relationship('products', 'slug')
            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', app()->getLocale()))
            ->multiple()
            ->searchable()
            ->preload();
    }

    public static function image(string $collection, bool $multiple = false, ?string $label = null): SpatieMediaLibraryFileUpload
    {
        $field = SpatieMediaLibraryFileUpload::make($collection)
            ->label($label ?? __("admin.content.fields.{$collection}"))
            ->collection($collection)
            ->image()
            ->acceptedFileTypes(self::IMAGE_TYPES)
            ->maxSize(8192)
            ->imageEditor();

        return $multiple ? $field->multiple()->reorderable()->maxFiles(24) : $field;
    }
}
