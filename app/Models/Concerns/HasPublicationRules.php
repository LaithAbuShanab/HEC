<?php

namespace App\Models\Concerns;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Domain\Catalog\Exceptions\PublicationBlocked;

trait HasPublicationRules
{
    abstract public function publicationBlockers(): array;

    public static function bootHasPublicationRules(): void
    {
        static::saving(function (self $model) {
            if (! $model->isDirty('review_status') || $model->review_status !== ReviewStatus::Published) {
                return;
            }

            $blockers = $model->publicationBlockers();

            if ($blockers !== []) {
                throw new PublicationBlocked($blockers);
            }
        });
    }

    protected function missingTranslations(array $attributes, array $locales = ['ar', 'en']): array
    {
        $missing = [];

        foreach ($attributes as $attribute) {
            foreach ($locales as $locale) {
                if (blank($this->getTranslation($attribute, $locale, false))) {
                    $missing[] = $attribute;
                    break;
                }
            }
        }

        return $missing;
    }
}
