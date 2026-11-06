<?php

namespace App\Domain\Catalog\Support;

final class SearchNormalizer
{
    private const MAP = [
        'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
        'ة' => 'ه', 'ى' => 'ي',
        'ؤ' => 'و', 'ئ' => 'ي',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٫' => '.', '٬' => '',
    ];

    public static function normalize(string $text): string
    {

        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $text) ?? $text;
        $text = strtr($text, self::MAP);
        $text = mb_strtolower($text, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    public static function expand(string $query, array $groups): array
    {
        $query = self::normalize($query);
        $tokens = array_filter(explode(' ', $query));
        $terms = [$query => true];

        foreach ($groups as $group) {
            $normalizedGroup = array_map(self::normalize(...), $group);
            $matches = in_array($query, $normalizedGroup, true)
                || array_intersect($tokens, $normalizedGroup) !== [];

            if ($matches) {
                foreach ($normalizedGroup as $term) {
                    $terms[$term] = true;
                }
            }
        }

        return array_keys($terms);
    }
}
