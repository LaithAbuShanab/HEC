<?php

namespace App\Models;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Concerns\HasReviewStatus;
use App\Models\Contracts\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Translatable\HasTranslations;

class Faq extends Model implements Translatable
{
    use HasReviewStatus, HasTranslations, LogsActivity, SoftDeletes;

    protected $fillable = ['question', 'answer', 'group', 'show_on_home', 'review_status', 'sort'];

    public array $translatable = ['question', 'answer'];

    protected function casts(): array
    {
        return [
            'review_status' => ReviewStatus::class,
            'show_on_home' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges();
    }
}
