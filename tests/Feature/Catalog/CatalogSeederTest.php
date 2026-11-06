<?php

use App\Domain\Catalog\Enums\Availability;
use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\HistoricalSpec;
use App\Models\Product;
use App\Models\SaleUnit;
use Database\Seeders\DatabaseSeeder;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('seeds the nine catalog categories and eight sale units', function () {
    expect(Category::count())->toBe(9)
        ->and(SaleUnit::count())->toBe(8);
});

it('accounts for all 43 appendix items', function () {
    $standalone = Product::whereNotNull('appendix_no')->pluck('appendix_no');
    $merged = Product::where('slug', 'galvanized-steel-coils')->first()->variants()->where('standard', 'G90')->exists();

    expect($standalone)->toHaveCount(42)
        ->and($standalone)->not->toContain('07')
        ->and($merged)->toBeTrue();
});

it('keeps ambiguous items as drafts and publishes nothing', function () {
    expect(Product::where('review_status', ReviewStatus::Draft)->count())->toBe(12)
        ->and(Product::published()->count())->toBe(0)
        ->and(Product::where('appendix_no', '43')->first()->review_status)->toBe(ReviewStatus::Draft);
});

it('never invents prices or availability', function () {
    expect(Product::whereNotNull('price')->count())->toBe(0)
        ->and(Product::where('availability', '!=', Availability::NeedsConfirmation)->count())->toBe(0)
        ->and(Product::whereNotNull('availability_updated_at')->count())->toBe(0);
});

it('stores bilingual names and normalized search text', function () {
    $product = Product::where('slug', 'galvanized-steel-coils')->first();

    expect($product->getTranslation('name', 'ar'))->toBe('رولات صاج مجلفن')
        ->and($product->getTranslation('name', 'en'))->toBe('Galvanized Steel Coils')
        ->and($product->search_text_en)->toContain('galvanized steel coils');
});

it('keeps historical specs as references only', function () {
    expect(HistoricalSpec::count())->toBe(9)
        ->and(HistoricalSpec::whereNull('product_id')->count())->toBe(1);
});

it('is idempotent', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Product::count())->toBe(42)
        ->and(Category::count())->toBe(9);
});
