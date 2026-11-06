<?php

namespace App\Domain\Catalog\Enums;

use App\Domain\Catalog\Enums\Concerns\HasBilingualLabels;

enum Availability: string
{
    use HasBilingualLabels;

    case NeedsConfirmation = 'needs_confirmation';
    case InStock = 'in_stock';
    case OnOrder = 'on_order';
    case TemporarilyUnavailable = 'temporarily_unavailable';

    protected const LABELS = [
        'needs_confirmation' => ['ar' => 'يحتاج تأكيد', 'en' => 'Needs confirmation'],
        'in_stock' => ['ar' => 'متوفر', 'en' => 'In stock'],
        'on_order' => ['ar' => 'حسب الطلب', 'en' => 'On order'],
        'temporarily_unavailable' => ['ar' => 'غير متوفر مؤقتًا', 'en' => 'Temporarily unavailable'],
    ];
}
