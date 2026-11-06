<?php

use App\Domain\Localization\Locale;
use App\Http\Controllers\Admin\DownloadQuoteAttachmentController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'language-select')->name('language.select');

Route::prefix('{locale}')
    ->where(['locale' => Locale::pattern()])
    ->group(function () {
        Route::inertia('/', 'home')->name('home');
    });

Route::get('admin/quote-attachments/{attachment}', DownloadQuoteAttachmentController::class)
    ->name('admin.quote-attachments.download');
