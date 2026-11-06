<?php

namespace App\Domain\Access;

enum Role: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Sales = 'sales';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
