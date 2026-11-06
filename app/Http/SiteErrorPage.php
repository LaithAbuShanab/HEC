<?php

namespace App\Http;

use App\Domain\Localization\Locale;
use App\Domain\Seo\SeoData;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class SiteErrorPage
{
    private const STATUSES = [403, 404, 410, 419, 429, 500, 503];

    public static function render(Response $response, Request $request): Response
    {
        $status = $response->getStatusCode();

        if (! in_array($status, self::STATUSES, true) || $request->expectsJson() || self::isExcluded($request)) {
            return $response;
        }

        if ($status >= 500 && config('app.debug')) {
            return $response;
        }

        $locale = Locale::tryFrom((string) $request->segment(1)) ?? Locale::Arabic;
        app()->setLocale($locale->value);

        Inertia::share(app(HandleInertiaRequests::class)->share($request));

        $title = __("site.errors.{$status}.title");

        $page = Inertia::render('error', [
            'status' => $status,
            'seo' => SeoData::make($title)->noindex()->alternates([])->toArray($request),
        ])->toResponse($request);

        $page->setStatusCode($status);
        $page->headers->set('X-Robots-Tag', 'noindex');

        return $page;
    }

    private static function isExcluded(Request $request): bool
    {
        foreach (['admin', 'livewire', 'filament', 'up'] as $prefix) {
            if ($request->is($prefix, "{$prefix}/*") || str_starts_with($request->path(), 'livewire-')) {
                return true;
            }
        }

        return false;
    }
}
