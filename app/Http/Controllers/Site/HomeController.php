<?php

namespace App\Http\Controllers\Site;

use App\Domain\Site\HomePageData;
use Inertia\Inertia;
use Inertia\Response;

class HomeController
{
    public function __invoke(HomePageData $data): Response
    {
        return Inertia::render('home', $data->forLocale(app()->getLocale()));
    }
}
