<?php

namespace App\Filament\Admin\Support;

use App\Domain\Localization\Locale;
use Closure;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

final class Bilingual
{
    public static function fields(string $name, string $label, Closure $factory, bool $required = false, int $columns = 2, bool $fillsSlug = false): Grid
    {
        $fields = [];

        foreach (Locale::cases() as $locale) {
            $field = $factory("{$name}.{$locale->value}")
                ->label($label.' ('.__("admin.locales.{$locale->value}").')')
                ->required($required)
                ->extraInputAttributes(['dir' => $locale->direction(), 'lang' => $locale->value]);

            if ($fillsSlug && $locale === Locale::English) {
                $field->live(onBlur: true)->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) {
                    if ($operation === 'create' && blank($get('slug')) && filled($state)) {
                        $set('slug', Str::slug($state));
                    }
                });
            }

            $fields[] = $field;
        }

        return Grid::make($columns)->schema($fields)->columnSpanFull();
    }
}
