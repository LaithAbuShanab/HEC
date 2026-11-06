<?php

namespace App\Filament\Admin\Support;

use App\Models\Contracts\Translatable;
use Illuminate\Database\Eloquent\Model;

final class Translations
{
    public static function fill(Model $record, array $data): array
    {
        if (! $record instanceof Translatable) {
            return $data;
        }

        foreach ($record->getTranslatableAttributes() as $attribute) {
            $data[$attribute] = $record->getTranslations($attribute);
        }

        return $data;
    }

    public static function apply(Model $record, array $data): array
    {
        if (! $record instanceof Translatable) {
            return $data;
        }

        foreach ($record->getTranslatableAttributes() as $attribute) {
            if (! array_key_exists($attribute, $data) || ! is_array($data[$attribute])) {
                continue;
            }

            $record->replaceTranslations($attribute, array_filter($data[$attribute], filled(...)));
            unset($data[$attribute]);
        }

        return $data;
    }
}
