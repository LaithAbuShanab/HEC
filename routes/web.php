<?php

use App\Domain\Localization\Locale;
use App\Http\Controllers\Admin\DownloadQuoteAttachmentController;
use App\Http\Controllers\Admin\SwitchPanelLocaleController;
use App\Http\Controllers\Site\HomeController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'language-select')->name('language.select');

Route::prefix('{locale}')
    ->where(['locale' => Locale::pattern()])
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');
    });

Route::get('admin/quote-attachments/{attachment}', DownloadQuoteAttachmentController::class)
    ->name('admin.quote-attachments.download');

Route::post('admin/locale/{locale}', SwitchPanelLocaleController::class)
    ->where('locale', Locale::pattern())
    ->middleware('throttle:20,1')
    ->name('admin.locale.switch');
