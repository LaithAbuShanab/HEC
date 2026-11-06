<?php

namespace App\Domain\Quotes\Enums;

use App\Domain\Shared\Concerns\HasBilingualLabels;

enum QuoteStatus: string
{
    use HasBilingualLabels;

    case New = 'new';
    case Reviewing = 'reviewing';
    case Contacted = 'contacted';
    case Quoted = 'quoted';
    case Closed = 'closed';
    case Spam = 'spam';

    protected const LABELS = [
        'new' => ['ar' => 'جديد', 'en' => 'New'],
        'reviewing' => ['ar' => 'قيد المراجعة', 'en' => 'Under review'],
        'contacted' => ['ar' => 'تم التواصل', 'en' => 'Contacted'],
        'quoted' => ['ar' => 'تم إرسال عرض', 'en' => 'Quote sent'],
        'closed' => ['ar' => 'مغلق', 'en' => 'Closed'],
        'spam' => ['ar' => 'مزعج', 'en' => 'Spam'],
    ];
}
