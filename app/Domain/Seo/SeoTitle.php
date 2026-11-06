<?php

namespace App\Domain\Seo;

use App\Settings\CompanySettings;

final class SeoTitle
{
    public static function brand(string $locale): string
    {
        $name = app(CompanySettings::class)->name[$locale] ?? 'HEC';

        return $locale === 'ar' ? "{$name} HEC" : $name;
    }

    public static function home(string $locale): string
    {
        return __('seo.home_title', ['brand' => self::brand($locale)], $locale);
    }

    public static function section(string $name, string $locale): string
    {
        return "{$name} | ".self::brand($locale);
    }

    public static function product(string $name, string $locale): string
    {
        return __('seo.product_title', ['name' => $name], $locale);
    }
}
