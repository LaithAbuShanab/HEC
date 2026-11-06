<?php

namespace App\Support;

use Filament\Support\Colors\Color;

final class BrandColors
{
    public const BLUE = [
        50 => '#EEF4FB',
        100 => '#D9E6F5',
        200 => '#B6CDEA',
        300 => '#8AAFDC',
        400 => '#5E8ECB',
        500 => '#4A7DBB',
        600 => '#3C6CA8',
        700 => '#30588A',
        800 => '#26466E',
        900 => '#1C3453',
        950 => '#0E1B30',
    ];

    public const GOLD = [
        50 => '#FDF8E8',
        100 => '#FAEFC6',
        200 => '#F5DE8C',
        300 => '#EFCB57',
        400 => '#E9BD3A',
        500 => '#E4B42A',
        600 => '#C2931B',
        700 => '#9B7116',
        800 => '#7A5815',
        900 => '#644815',
        950 => '#3A2808',
    ];

    public static function palette(array $shades): array
    {
        return array_map(fn (string $hex) => Color::convertToOklch($hex), $shades);
    }
}
