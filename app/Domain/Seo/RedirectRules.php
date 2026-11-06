<?php

namespace App\Domain\Seo;

use App\Models\Redirect;

final class RedirectRules
{
    public const PROTECTED_PREFIXES = ['/admin', '/livewire', '/filament'];

    public static function fromPathError(string $fromPath, ?int $ignoreId = null): ?string
    {
        if (! str_starts_with($fromPath, '/')) {
            return 'format';
        }

        $normalized = Redirect::normalizePath($fromPath);

        foreach (self::PROTECTED_PREFIXES as $prefix) {
            if ($normalized === $prefix || str_starts_with($normalized, $prefix.'/')) {
                return 'protected';
            }
        }

        $duplicate = Redirect::query()->where('from_path', $normalized)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists();

        return $duplicate ? 'duplicate' : null;
    }

    public static function toPathError(string $toPath, string $fromPath, ?int $ignoreId = null): ?string
    {
        $isInternal = str_starts_with($toPath, '/') && ! str_starts_with($toPath, '//');

        if (! $isInternal && ! str_starts_with($toPath, 'https://')) {
            return 'format';
        }

        if (! $isInternal) {
            return null;
        }

        $target = Redirect::normalizePath($toPath);

        if ($target === Redirect::normalizePath($fromPath)) {
            return 'same';
        }

        $chained = Redirect::query()->where('from_path', $target)->where('is_active', true)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists();

        return $chained ? 'chain' : null;
    }
}
