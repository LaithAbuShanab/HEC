<?php

namespace App\Http\Middleware;

use App\Domain\Company\CompanyProfile;
use App\Domain\Localization\Locale;
use App\Domain\Localization\LocalizedUrl;
use App\Domain\Quotes\QuoteCartManager;
use App\Domain\Site\SiteNavigation;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $locale = Locale::from(app()->getLocale());

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'locale' => $locale->value,
            'direction' => $locale->direction(),
            'alternates' => fn () => LocalizedUrl::alternates($request),
            'company' => fn () => app(CompanyProfile::class)->toArray($locale->value),
            'nav' => fn () => app(SiteNavigation::class)->forLocale($locale->value),
            'ui' => fn () => __('site'),
            'quote' => fn () => ['count' => app(QuoteCartManager::class)->count($request)],
            'flash' => fn () => $request->hasSession() ? array_filter([
                'quoteAdded' => $request->session()->get('quote_added'),
                'quoteRemoved' => $request->session()->get('quote_removed'),
            ]) : [],
        ];
    }
}
