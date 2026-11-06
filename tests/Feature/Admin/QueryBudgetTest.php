<?php

use App\Domain\Access\Role;
use App\Filament\Admin\Resources\Activities\Pages\ListActivities;
use App\Filament\Admin\Resources\Categories\Pages\ListCategories;
use App\Filament\Admin\Resources\Products\Pages\EditProduct;
use App\Filament\Admin\Resources\Products\Pages\ListProducts;
use App\Filament\Admin\Resources\QuoteRequests\Pages\ListQuoteRequests;
use App\Filament\Admin\Resources\QuoteRequests\Pages\ViewQuoteRequest;
use App\Filament\Admin\Resources\Services\Pages\ListServices;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Filament\Admin\Widgets\CatalogHealthStats;
use App\Filament\Admin\Widgets\LatestQuotesTable;
use App\Filament\Admin\Widgets\ProductsNeedingAttentionTable;
use App\Filament\Admin\Widgets\QuoteStatsWidget;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    QuoteRequest::factory()->count(15)->withItems()->create();
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin->value);
    $this->actingAs($admin);
});

it('renders admin screens within a fixed query budget', function (string $component, Closure $parameters, int $budget) {
    $parameters = $parameters();
    $queries = 0;
    DB::listen(function () use (&$queries) {
        $queries++;
    });

    Livewire::test($component, $parameters)->assertOk();

    expect($queries)->toBeLessThanOrEqual($budget, "{$component} ran {$queries} queries (budget {$budget})");
})->with([
    'product list' => [ListProducts::class, fn () => [], 8],
    'product edit' => [EditProduct::class, fn () => ['record' => Product::first()->getRouteKey()], 18],
    'category list' => [ListCategories::class, fn () => [], 5],
    'service list' => [ListServices::class, fn () => [], 5],
    'quote list' => [ListQuoteRequests::class, fn () => [], 4],
    'quote view' => [ViewQuoteRequest::class, fn () => ['record' => QuoteRequest::first()->getRouteKey()], 9],
    'user list' => [ListUsers::class, fn () => [], 5],
    'activity log' => [ListActivities::class, fn () => [], 8],
    'products needing work' => [ProductsNeedingAttentionTable::class, fn () => [], 5],
    'latest quotes' => [LatestQuotesTable::class, fn () => [], 2],
    'catalog health' => [CatalogHealthStats::class, fn () => [], 4],
    'quote stats' => [QuoteStatsWidget::class, fn () => [], 5],
]);
