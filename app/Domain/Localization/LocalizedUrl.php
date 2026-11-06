<?php

namespace App\Domain\Localization;

use Illuminate\Http\Request;

final class LocalizedUrl
{
    public static function swap(Request $request, Locale $target): string
    {
        $route = $request->route();

        if ($route === null || $route->parameter('locale') === null || $route->getName() === null) {
            return url($target->value);
        }

        $parameters = array_intersect_key($route->parameters(), array_flip($route->parameterNames()));

        return route($route->getName(), ['locale' => $target->value] + $parameters);
    }

    public static function alternates(Request $request): array
    {
        $alternates = [];
        foreach (Locale::cases() as $locale) {
            $alternates[$locale->value] = self::swap($request, $locale);
        }

        return $alternates;
    }
}
