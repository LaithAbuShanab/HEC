<?php

namespace App\Domain\Catalog\Exchange;

final class ProductSheet
{
    public const TRANSLATED = [
        'name' => 'name',
        'short_description' => 'short_description',
        'uses' => 'uses',
    ];

    public const PLAIN = ['sku', 'grade', 'standard', 'brand', 'model'];

    public const LIST_SEPARATOR = '|';

    public static function headers(): array
    {
        $headers = ['slug', 'appendix_no'];

        foreach (array_keys(self::TRANSLATED) as $attribute) {
            $headers[] = "{$attribute}_ar";
            $headers[] = "{$attribute}_en";
        }

        return [
            ...$headers,
            'category',
            ...self::PLAIN,
            'sale_unit', 'condition', 'availability', 'price', 'review_status', 'missing_fields',
        ];
    }

    public static function splitList(?string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(self::LIST_SEPARATOR, (string) $value)), 'filled'));
    }
}
