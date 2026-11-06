<?php

namespace App\Http\Middleware;

use App\Domain\Localization\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $parameter = $request->route()?->parameter('locale');

        if ($parameter !== null) {
            $locale = is_string($parameter) ? Locale::tryFrom($parameter) : null;
            abort_if($locale === null, 404);

            app()->setLocale($locale->value);
            URL::defaults(['locale' => $locale->value]);
        }

        return $next($request);
    }
}
