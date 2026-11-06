<?php

namespace App\Filament\Admin\Support;

use App\Models\SaleUnit;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;

final class Display
{
    public static function ltr(mixed $value): ?HtmlString
    {
        if (blank($value)) {
            return null;
        }

        return new HtmlString('<bdi dir="ltr">'.e((string) $value).'</bdi>');
    }

    public static function quantity(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
    }

    public static function unit(?string $key): ?string
    {
        if (blank($key)) {
            return null;
        }

        $units = once(fn () => SaleUnit::query()->get()->mapWithKeys(fn (SaleUnit $unit) => [
            $unit->key => $unit->getTranslation('name', app()->getLocale()),
        ])->all());

        return $units[$key] ?? $key;
    }

    public static function missingField(string $key): string
    {
        return Lang::has("admin.missing_fields.{$key}") ? __("admin.missing_fields.{$key}") : str_replace('_', ' ', $key);
    }

    public static function missingFields(?array $keys): array
    {
        return array_map(self::missingField(...), $keys ?? []);
    }

    public static function sourceLine(string $key, string $value): HtmlString
    {
        $label = Lang::has("admin.quote_source.{$key}") ? __("admin.quote_source.{$key}") : $key;

        return new HtmlString(e($label).': <bdi dir="ltr">'.e($value).'</bdi>');
    }

    public static function knownMissingFields(): array
    {
        $labels = __('admin.missing_fields');

        return is_array($labels) ? $labels : [];
    }
}
