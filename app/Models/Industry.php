<?php

namespace App\Models;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Concerns\HasPublicationRules;
use App\Models\Concerns\HasReviewStatus;
use App\Models\Concerns\HasWebImages;
use App\Models\Contracts\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Industry extends Model implements HasMedia, Translatable
{
    use HasPublicationRules, HasReviewStatus, HasTranslations, HasWebImages, InteractsWithMedia, LogsActivity, SoftDeletes {
        HasWebImages::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = ['slug', 'title', 'summary', 'body', 'review_status', 'sort', 'seo', 'published_at'];

    public array $translatable = ['title', 'summary', 'body'];

    protected function casts(): array
    {
        return [
            'review_status' => ReviewStatus::class,
            'seo' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('sort')->orderByPivot('sort');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function publicationBlockers(): array
    {
        return $this->missingTranslations(['title', 'summary']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }
}
