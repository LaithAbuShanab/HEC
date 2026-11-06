<?php

namespace App\Models;

use App\Domain\Catalog\Enums\Availability;
use App\Domain\Catalog\Enums\ProductCondition;
use App\Domain\Catalog\Enums\ReviewStatus;
use App\Domain\Catalog\Support\SearchNormalizer;
use App\Models\Concerns\HasReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements HasMedia
{
    use HasReviewStatus, HasTranslations, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = [
        'appendix_no', 'sku', 'slug', 'primary_category_id',
        'name', 'short_description', 'description', 'uses',
        'material', 'grade', 'standard', 'origin', 'brand', 'model',
        'sale_unit_id', 'condition', 'availability', 'availability_updated_at',
        'price', 'price_currency', 'review_status', 'missing_fields',
        'source_reference', 'source_reviewed_at', 'is_featured', 'sort', 'seo', 'published_at',
    ];

    protected $hidden = ['search_text_ar', 'search_text_en'];

    protected $attributes = [
        'condition' => 'unconfirmed',
        'availability' => 'needs_confirmation',
        'review_status' => 'draft',
        'price_currency' => 'JOD',
    ];

    public array $translatable = ['name', 'short_description', 'description', 'uses', 'material', 'origin'];

    protected function casts(): array
    {
        return [
            'review_status' => ReviewStatus::class,
            'condition' => ProductCondition::class,
            'availability' => Availability::class,
            'availability_updated_at' => 'datetime',
            'source_reviewed_at' => 'date',
            'published_at' => 'datetime',
            'price' => 'decimal:3',
            'missing_fields' => 'array',
            'seo' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->uuid ??= (string) Str::uuid();
        });

        static::saving(function (Product $product) {
            $availabilityChanged = $product->exists
                ? $product->isDirty('availability')
                : $product->availability !== Availability::NeedsConfirmation;

            if ($availabilityChanged) {
                $product->availability_updated_at = now();
            }

            foreach (['ar', 'en'] as $locale) {
                $product->{"search_text_{$locale}"} = $product->buildSearchText($locale);
            }
        });
    }

    public function buildSearchText(string $locale): string
    {
        $parts = [
            $this->getTranslation('name', $locale, false),
            $this->getTranslation('short_description', $locale, false),
            $this->getTranslation('uses', $locale, false),
            $this->sku, $this->brand, $this->model, $this->standard, $this->grade,
        ];

        return SearchNormalizer::normalize(implode(' ', array_filter($parts)));
    }

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot('sort');
    }

    public function saleUnit(): BelongsTo
    {
        return $this->belongsTo(SaleUnit::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort');
    }

    public function specValues(): HasMany
    {
        return $this->hasMany(ProductSpecValue::class);
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'product_related', 'product_id', 'related_product_id')
            ->withPivot('sort')
            ->orderByPivot('sort');
    }

    public function historicalSpecs(): HasMany
    {
        return $this->hasMany(HistoricalSpec::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('gallery')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('documents')->acceptsMimeTypes(['application/pdf']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }
}
