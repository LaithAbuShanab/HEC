<?php

namespace App\Models;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Concerns\HasPublicationRules;
use App\Models\Concerns\HasReviewStatus;
use App\Models\Concerns\HasWebImages;
use App\Models\Contracts\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Category extends Model implements HasMedia, Translatable
{
    use HasPublicationRules, HasReviewStatus, HasTranslations, HasWebImages, InteractsWithMedia, LogsActivity, SoftDeletes {
        HasWebImages::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = ['parent_id', 'slug', 'name', 'description', 'sort', 'review_status', 'seo'];

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'review_status' => ReviewStatus::class,
            'seo' => 'array',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function primaryProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'primary_category_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('sort');
    }

    public function publicationBlockers(): array
    {
        return $this->missingTranslations(['name', 'description']);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }
}
