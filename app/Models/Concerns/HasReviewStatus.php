<?php

namespace App\Models\Concerns;

use App\Domain\Catalog\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasReviewStatus
{
    public static function bootHasReviewStatus(): void
    {
        static::saving(function (self $model) {
            if ($model->isDirty('review_status')
                && $model->review_status === ReviewStatus::Published
                && $model->isFillable('published_at')
                && $model->getAttribute('published_at') === null) {
                $model->setAttribute('published_at', now());
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('review_status'), ReviewStatus::Published);
    }

    public function isPublished(): bool
    {
        return $this->review_status === ReviewStatus::Published;
    }
}
