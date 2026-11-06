<?php

namespace App\Domain\Access;

use App\Domain\Shared\Concerns\HasBilingualLabels;

enum Role: string
{
    use HasBilingualLabels;

    case Admin = 'admin';
    case Editor = 'editor';
    case Sales = 'sales';

    protected const LABELS = [
        'admin' => ['ar' => 'مدير', 'en' => 'Administrator'],
        'editor' => ['ar' => 'محرر', 'en' => 'Editor'],
        'sales' => ['ar' => 'مبيعات', 'en' => 'Sales'],
    ];

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
