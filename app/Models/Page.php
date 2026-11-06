<?php

namespace App\Models;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Concerns\HasPublicationRules;
use App\Models\Concerns\HasReviewStatus;
use App\Models\Contracts\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\HasTranslations;

class Page extends Model implements Translatable
{
    use HasPublicationRules, HasReviewStatus, HasTranslations, LogsActivity, SoftDeletes;

    public const KEYS = ['about', 'privacy', 'terms'];

    protected $fillable = ['key', 'title', 'body', 'review_status', 'seo', 'published_at'];

    public array $translatable = ['title', 'body'];

    protected function casts(): array
    {
        return [
            'review_status' => ReviewStatus::class,
            'seo' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function publicationBlockers(): array
    {
        return $this->missingTranslations(['title', 'body']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }
}
