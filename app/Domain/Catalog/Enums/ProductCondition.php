<?php

namespace App\Domain\Catalog\Enums;

use App\Domain\Shared\Concerns\HasBilingualLabels;

enum ProductCondition: string
{
    use HasBilingualLabels;

    case Unconfirmed = 'unconfirmed';
    case New = 'new';
    case Used = 'used';

    protected const LABELS = [
        'unconfirmed' => ['ar' => 'غير مؤكد', 'en' => 'Unconfirmed'],
        'new' => ['ar' => 'جديد', 'en' => 'New'],
        'used' => ['ar' => 'مستعمل', 'en' => 'Used'],
    ];
}
