<?php

namespace App\Domain\Localization;

enum Locale: string
{
    case Arabic = 'ar';
    case English = 'en';

    public function direction(): string
    {
        return $this === self::Arabic ? 'rtl' : 'ltr';
    }

    public function other(): self
    {
        return $this === self::Arabic ? self::English : self::Arabic;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function pattern(): string
    {
        return implode('|', self::values());
    }
}
