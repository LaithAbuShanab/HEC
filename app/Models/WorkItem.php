<?php

namespace App\Models;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Concerns\HasReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class WorkItem extends Model implements HasMedia
{
    use HasReviewStatus, HasTranslations, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = ['slug', 'title', 'summary', 'body', 'performed_on', 'review_status', 'sort', 'seo', 'published_at'];

    public array $translatable = ['title', 'summary', 'body'];

    protected function casts(): array
    {
        return [
            'review_status' => ReviewStatus::class,
            'seo' => 'array',
            'published_at' => 'datetime',
            'performed_on' => 'date',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('sort')->orderByPivot('sort');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }
}
