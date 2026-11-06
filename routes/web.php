<?php

use App\Domain\Localization\Locale;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'language-select')->name('language.select');

Route::prefix('{locale}')
    ->where(['locale' => Locale::pattern()])
    ->group(function () {
        Route::inertia('/', 'home')->name('home');
    });
