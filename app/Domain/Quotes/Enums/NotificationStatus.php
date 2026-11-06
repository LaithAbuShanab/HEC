<?php

namespace App\Domain\Quotes\Enums;

use App\Domain\Shared\Concerns\HasBilingualLabels;

enum NotificationStatus: string
{
    use HasBilingualLabels;

    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';

    protected const LABELS = [
        'pending' => ['ar' => 'بانتظار الإرسال', 'en' => 'Pending'],
        'sent' => ['ar' => 'أُرسل', 'en' => 'Sent'],
        'failed' => ['ar' => 'فشل الإرسال', 'en' => 'Failed'],
    ];
}
