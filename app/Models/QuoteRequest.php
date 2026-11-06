<?php

namespace App\Models;

use App\Domain\Quotes\Enums\FulfillmentMethod;
use App\Domain\Quotes\Enums\NotificationStatus;
use App\Domain\Quotes\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class QuoteRequest extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public const REFERENCE_PREFIX = 'HEC';

    protected $fillable = [
        'idempotency_key', 'name', 'phone', 'company', 'email', 'city', 'fulfillment', 'needed_by',
        'notes', 'privacy_accepted_at', 'locale', 'status', 'notification_status', 'notification_attempts',
        'notified_at', 'last_notification_error', 'source', 'ip_hash', 'assigned_to', 'internal_notes',
    ];

    protected $hidden = ['ip_hash', 'idempotency_key'];

    protected $attributes = [
        'status' => 'new',
        'notification_status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuoteStatus::class,
            'notification_status' => NotificationStatus::class,
            'fulfillment' => FulfillmentMethod::class,
            'needed_by' => 'date',
            'privacy_accepted_at' => 'datetime',
            'notified_at' => 'datetime',
            'source' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (QuoteRequest $quote) {
            $quote->reference = self::formatReference($quote->id, (int) $quote->created_at?->format('Y'));
            $quote->saveQuietly();
        });
    }

    public static function formatReference(int $id, int $year): string
    {
        return sprintf('%s-%d-%06d', self::REFERENCE_PREFIX, $year, $id);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class)->orderBy('sort');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(QuoteAttachment::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'assigned_to', 'internal_notes'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
