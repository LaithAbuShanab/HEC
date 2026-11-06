<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasWebImages
{
    public const IMAGE_SIZES = ['thumb' => 240, 'card' => 720, 'large' => 1600];

    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media !== null && ! str_starts_with((string) $media->mime_type, 'image/')) {
            return;
        }

        foreach (self::IMAGE_SIZES as $name => $width) {
            $this->addMediaConversion($name)
                ->nonQueued()
                ->format('webp')
                ->quality(82)
                ->fit(Fit::Max, $width, $width);
        }
    }

    public function imageFor(string $collection, string $locale): ?array
    {
        $media = $this->getFirstMedia($collection);

        return $media === null ? null : self::presentImage($media, $locale);
    }

    public static function presentImage(Media $media, string $locale): array
    {
        $srcset = [];

        foreach (self::IMAGE_SIZES as $name => $width) {
            if ($media->hasGeneratedConversion($name)) {
                $srcset[] = $media->getUrl($name).' '.$width.'w';
            }
        }

        return [
            'src' => $media->hasGeneratedConversion('card') ? $media->getUrl('card') : $media->getUrl(),
            'srcset' => $srcset === [] ? null : implode(', ', $srcset),
            'alt' => (string) ($media->getCustomProperty("alt.{$locale}") ?? ''),
            'width' => $media->getCustomProperty('width'),
            'height' => $media->getCustomProperty('height'),
        ];
    }
}
