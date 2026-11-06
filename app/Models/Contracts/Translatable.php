<?php

namespace App\Models\Contracts;

interface Translatable
{
    public function getTranslatableAttributes(): array;

    public function getTranslation(string $key, string $locale, bool $useFallbackLocale = true): mixed;

    public function getTranslations(?string $key = null, ?array $allowedLocales = null): array;

    public function replaceTranslations(string $key, array $translations): self;
}
