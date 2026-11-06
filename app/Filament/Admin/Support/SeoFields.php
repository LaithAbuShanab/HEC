<?php

namespace App\Filament\Admin\Support;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

final class SeoFields
{
    public static function section(): Section
    {
        return Section::make(__('admin.common.seo'))
            ->description(__('admin.common.seo_help'))
            ->collapsed()
            ->schema([
                Bilingual::fields('seo.title', __('admin.common.seo_title'), fn (string $path) => TextInput::make($path)->maxLength(70)),
                Bilingual::fields('seo.description', __('admin.common.seo_description'), fn (string $path) => Textarea::make($path)->rows(2)->maxLength(170)),
                Toggle::make('seo.noindex')->label(__('admin.common.seo_noindex')),
            ]);
    }
}
