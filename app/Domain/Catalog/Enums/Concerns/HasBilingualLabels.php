<?php

namespace App\Domain\Catalog\Enums\Concerns;

trait HasBilingualLabels
{
    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return self::LABELS[$this->value][$locale] ?? self::LABELS[$this->value]['en'];
    }

    public static function options(?string $locale = null): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label($locale);
        }

        return $options;
    }
}
