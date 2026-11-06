<?php

use App\Domain\Localization\Locale;
use App\Http\Controllers\Admin\DownloadQuoteAttachmentController;
use App\Http\Controllers\Admin\SwitchPanelLocaleController;
use App\Http\Controllers\Site\CatalogController;
use App\Http\Controllers\Site\ContentController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\QuoteCartController;
use App\Http\Controllers\Site\QuoteRequestController;
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
        Route::get('services', [ContentController::class, 'services'])->name('services.index');
        Route::get('services/{slug}', [ContentController::class, 'service'])->where('slug', '[a-z0-9-]+')->name('services.show');
        Route::get('industries', [ContentController::class, 'industries'])->name('industries.index');
        Route::get('guides', [ContentController::class, 'guides'])->name('guides.index');
        Route::get('guides/{slug}', [ContentController::class, 'guide'])->where('slug', '[a-z0-9-]+')->name('guides.show');
        Route::get('work', [ContentController::class, 'work'])->name('work.index');
        Route::get('faq', [ContentController::class, 'faq'])->name('faq');
        Route::get('contact', [ContentController::class, 'contact'])->name('contact');
        Route::get('request-quote', [QuoteRequestController::class, 'create'])->name('quote.create');
        Route::post('request-quote', [QuoteRequestController::class, 'store'])->middleware('throttle:quote-submit')->name('quote.store');
        Route::get('request-quote/success', [QuoteRequestController::class, 'success'])->name('quote.success');
        Route::middleware('throttle:quote-cart')->group(function () {
            Route::post('request-quote/items', [QuoteCartController::class, 'store'])->name('quote.items.store');
            Route::patch('request-quote/items/{item}', [QuoteCartController::class, 'update'])->whereNumber('item')->name('quote.items.update');
            Route::delete('request-quote/items/{item}', [QuoteCartController::class, 'destroy'])->whereNumber('item')->name('quote.items.destroy');
        });
        Route::get('{key}', [ContentController::class, 'page'])->whereIn('key', ['about', 'privacy', 'terms'])->name('page');
    });

Route::get('admin/quote-attachments/{attachment}', DownloadQuoteAttachmentController::class)
    ->name('admin.quote-attachments.download');

Route::post('admin/locale/{locale}', SwitchPanelLocaleController::class)
    ->where('locale', Locale::pattern())
    ->middleware('throttle:20,1')
    ->name('admin.locale.switch');
