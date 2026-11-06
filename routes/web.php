<?php

use App\Domain\Localization\Locale;
use App\Http\Controllers\Admin\DownloadQuoteAttachmentController;
use App\Http\Controllers\Admin\SwitchPanelLocaleController;
use App\Http\Controllers\Site\CatalogController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\SearchController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'language-select')->name('language.select');

Route::prefix('{locale}')
    ->where(['locale' => Locale::pattern()])
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::get('products', [CatalogController::class, 'index'])->name('products.index');
        Route::get('products/{category}', [CatalogController::class, 'category'])->where('category', '[a-z0-9-]+')->name('products.category');
        Route::get('products/{category}/{product}', [CatalogController::class, 'product'])->where(['category' => '[a-z0-9-]+', 'product' => '[a-z0-9-]+'])->name('products.show');
        Route::get('search', SearchController::class)->middleware('throttle:60,1')->name('search');
    });

Route::get('admin/quote-attachments/{attachment}', DownloadQuoteAttachmentController::class)
    ->name('admin.quote-attachments.download');

Route::post('admin/locale/{locale}', SwitchPanelLocaleController::class)
    ->where('locale', Locale::pattern())
    ->middleware('throttle:20,1')
    ->name('admin.locale.switch');
