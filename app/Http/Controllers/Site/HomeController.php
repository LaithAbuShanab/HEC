<?php

namespace App\Http\Controllers\Site;

use App\Domain\Seo\OrganizationSchema;
use App\Domain\Seo\SeoData;
use App\Domain\Seo\SeoTitle;
use App\Domain\Site\HomePageData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController
{
    public function __invoke(Request $request, HomePageData $data): Response
    {
        $locale = app()->getLocale();

        $seo = SeoData::make(SeoTitle::home($locale), __('seo.home_description'));

        foreach (OrganizationSchema::build($locale) as $schema) {
            $seo->schema($schema);
        }

        return Inertia::render('home', [
            ...$data->forLocale($locale),
            'seo' => $seo->toArray($request),
        ]);
    }
}
