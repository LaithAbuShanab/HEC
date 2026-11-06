<?php

namespace App\Filament\Admin\Support;

use App\Domain\Localization\Locale;
use Closure;
use Filament\Schemas\Components\Grid;

final class Bilingual
{
    public static function fields(string $name, string $label, Closure $factory, bool $required = false, int $columns = 2): Grid
    {
        $fields = [];

        foreach (Locale::cases() as $locale) {
            $fields[] = $factory("{$name}.{$locale->value}")
                ->label($label.' ('.__("admin.locales.{$locale->value}").')')
                ->required($required)
                ->extraInputAttributes(['dir' => $locale->direction(), 'lang' => $locale->value]);
        }

        return Grid::make($columns)->schema($fields)->columnSpanFull();
    }
}
