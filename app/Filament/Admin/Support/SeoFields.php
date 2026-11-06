<?php

namespace App\Filament\Admin\Support;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

final class SeoFields
{
    private static function counted(TextInput|Textarea $field, int $ideal, int $max): TextInput|Textarea
    {
        return $field
            ->maxLength($max)
            ->live(debounce: 600)
            ->hint(fn (?string $state) => __('admin.common.characters', ['count' => mb_strlen((string) $state), 'max' => $ideal]))
            ->hintColor(fn (?string $state) => mb_strlen((string) $state) > $ideal ? 'warning' : 'gray');
    }

    public static function section(): Section
    {
        return Section::make(__('admin.common.seo'))
            ->description(__('admin.common.seo_help'))
            ->collapsed()
            ->schema([
                Bilingual::fields('seo.title', __('admin.common.seo_title'), fn (string $path) => self::counted(TextInput::make($path), 60, 70)),
                Bilingual::fields('seo.description', __('admin.common.seo_description'), fn (string $path) => self::counted(Textarea::make($path)->rows(2), 155, 170)),
                Toggle::make('seo.noindex')->label(__('admin.common.seo_noindex')),
            ]);
    }
}
