<?php

namespace App\Domain\Seo;

use App\Domain\Localization\Locale;
use App\Domain\Localization\LocalizedUrl;
use App\Settings\CompanySettings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class SeoData
{
    private array $jsonLd = [];

    private bool $indexable = true;

    private ?string $image = null;

    private string $type = 'website';

    private ?array $alternates = null;

    public function __construct(
        private string $title,
        private ?string $description = null,
        private ?string $canonical = null,
    ) {}

    public static function make(string $title, ?string $description = null): self
    {
        return new self($title, $description);
    }

    public function overrides(?array $seo, string $locale): self
    {
        $title = $seo['title'][$locale] ?? null;
        $description = $seo['description'][$locale] ?? null;

        if (filled($title)) {
            $this->title = (string) $title;
        }

        if (filled($description)) {
            $this->description = (string) $description;
        }

        if (! empty($seo['noindex'])) {
            $this->indexable = false;
        }

        return $this;
    }

    public function canonical(string $url): self
    {
        $this->canonical = $url;

        return $this;
    }

    public function alternates(array $alternates): self
    {
        $this->alternates = $alternates;

        return $this;
    }

    public function noindex(bool $condition = true): self
    {
        if ($condition) {
            $this->indexable = false;
        }

        return $this;
    }

    public function image(?string $url): self
    {
        $this->image = $url;

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function schema(array $schema): self
    {
        $this->jsonLd[] = ['@context' => 'https://schema.org', ...$schema];

        return $this;
    }

    public function toArray(Request $request): array
    {
        $alternates = $this->alternates ?? LocalizedUrl::alternates($request);
        $locale = app()->getLocale();
        $canonical = $this->canonical ?? ($alternates[$locale] ?? $request->url());

        return [
            'title' => $this->title,
            'description' => $this->description === null ? null : Str::limit(trim(strip_tags($this->description)), 170, '…'),
            'canonical' => $canonical,
            'alternates' => $this->indexable ? [...$alternates, 'x-default' => $alternates[Locale::Arabic->value] ?? $canonical] : [],
            'robots' => $this->indexable ? 'index, follow' : 'noindex, follow',
            'image' => $this->image ?? url('/images/brand/og-default.jpg'),
            'type' => $this->type,
            'locale' => $locale === 'ar' ? 'ar_JO' : 'en_US',
            'siteName' => app(CompanySettings::class)->name[$locale] ?? 'HEC',
            'jsonLd' => $this->jsonLd,
        ];
    }
}
