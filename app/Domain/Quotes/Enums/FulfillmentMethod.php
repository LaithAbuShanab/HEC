<?php

namespace App\Domain\Quotes\Enums;

use App\Domain\Shared\Concerns\HasBilingualLabels;

enum FulfillmentMethod: string
{
    use HasBilingualLabels;

    case Pickup = 'pickup';
    case Delivery = 'delivery';
    case Undecided = 'undecided';

    protected const LABELS = [
        'pickup' => ['ar' => 'استلام من المستودع', 'en' => 'Pickup'],
        'delivery' => ['ar' => 'توصيل', 'en' => 'Delivery'],
        'undecided' => ['ar' => 'لم يحدد بعد', 'en' => 'Not decided yet'],
    ];
}
