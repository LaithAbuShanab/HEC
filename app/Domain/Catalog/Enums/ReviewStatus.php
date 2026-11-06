<?php

namespace App\Domain\Catalog\Enums;

use App\Domain\Catalog\Enums\Concerns\HasBilingualLabels;

enum ReviewStatus: string
{
    use HasBilingualLabels;

    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';

    protected const LABELS = [
        'draft' => ['ar' => 'مسودة', 'en' => 'Draft'],
        'pending' => ['ar' => 'بانتظار اعتماد', 'en' => 'Pending approval'],
        'published' => ['ar' => 'منشور', 'en' => 'Published'],
    ];
}
