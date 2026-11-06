<?php

use App\Domain\Catalog\CatalogSearch;
use App\Domain\Catalog\Enums\Availability;
use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\SearchLog;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);
});

function openCategory(string $slug): Category
{
    $category = Category::where('slug', $slug)->first();
    $category->update(['description' => ['ar' => 'وصف التصنيف', 'en' => 'Category description'], 'review_status' => ReviewStatus::Published]);

    return $category;
}

function publishProduct(string $slug, array $attributes = []): Product
{
    $product = Product::where('slug', $slug)->first();
    $product->update(['short_description' => ['ar' => 'وصف مختصر', 'en' => 'Short description'], ...$attributes]);
    $product->addMedia(UploadedFile::fake()->image("{$slug}.jpg", 1200, 900))->toMediaCollection('main');
    $product->update(['review_status' => ReviewStatus::Published]);

    return $product->fresh();
}

it('returns 404 for unpublished categories and products', function () {
    $this->get('/ar/products/pipes-tubes')->assertNotFound();
    $this->get('/ar/products/pipes-tubes/black-steel-pipes')->assertNotFound();

    openCategory('pipes-tubes');

    $this->get('/ar/products/pipes-tubes')->assertOk();
    $this->get('/ar/products/pipes-tubes/black-steel-pipes')->assertNotFound();
});

it('lists only published products in a category with crawlable seo', function () {
    openCategory('pipes-tubes');
    publishProduct('black-steel-pipes');

    $this->get('/ar/products/pipes-tubes')->assertInertia(fn (Assert $page) => $page
        ->component('products/category')
        ->has('products.data', 1)
        ->where('products.data.0.slug', 'black-steel-pipes')
        ->where('resultsLabel', 'نتيجة واحدة')
        ->where('seo.robots', 'index, follow')
        ->where('seo.canonical', url('/ar/products/pipes-tubes'))
        ->where('seo.alternates.en', url('/en/products/pipes-tubes'))
        ->where('seo.alternates.x-default', url('/ar/products/pipes-tubes'))
        ->where('seo.jsonLd.0.@type', 'BreadcrumbList'));
});

it('builds filters only from real data and keeps filtered pages out of the index', function () {
    openCategory('pipes-tubes');
    publishProduct('black-steel-pipes', ['availability' => Availability::InStock]);
    publishProduct('galvanized-steel-pipes', ['availability' => Availability::OnOrder]);
    Product::where('slug', 'galvanized-steel-pipes')->first()->variants()->create(['label' => ['ar' => '2 إنش', 'en' => '2 inch'], 'thickness_mm' => 1.5]);

    $this->get('/en/products/pipes-tubes')->assertInertia(fn (Assert $page) => $page
        ->where('facets.0.key', 'availability')
        ->has('facets', 1)
        ->has('facets.0.options', 2));

    $this->get('/en/products/pipes-tubes?availability=in_stock')->assertInertia(fn (Assert $page) => $page
        ->has('products.data', 1)
        ->where('products.data.0.slug', 'black-steel-pipes')
        ->where('filtered', true)
        ->where('seo.robots', 'noindex, follow')
        ->where('seo.canonical', url('/en/products/pipes-tubes'))
        ->where('seo.alternates', []));
});

it('shows a product with its specs, variants and a 301 from a secondary category', function () {
    openCategory('pipes-tubes');
    openCategory('sheets-coils');
    $product = publishProduct('galvanized-steel-pipes', ['grade' => 'S235', 'standard' => 'EN 10255']);
    $product->categories()->attach(Category::where('slug', 'sheets-coils')->first());

    $this->get('/ar/products/pipes-tubes/galvanized-steel-pipes')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('products/show')
        ->where('product.name', 'مواسير حديد مجلفنة')
        ->where('product.specs.0.value', 'S235')
        ->where('seo.type', 'product')
        ->where('seo.jsonLd.1.@type', 'Product')
        ->missing('seo.jsonLd.1.offers'));

    $this->get('/ar/products/sheets-coils/galvanized-steel-pipes')->assertRedirect(url('/ar/products/pipes-tubes/galvanized-steel-pipes'))->assertStatus(301);
});

it('adds an offer to structured data only when a real price exists', function () {
    openCategory('pipes-tubes');
    publishProduct('black-steel-pipes', ['price' => 12.5, 'availability' => Availability::InStock]);

    $this->get('/en/products/pipes-tubes/black-steel-pipes')->assertInertia(fn (Assert $page) => $page
        ->where('seo.jsonLd.1.offers.price', '12.500')
        ->where('seo.jsonLd.1.offers.priceCurrency', 'JOD')
        ->where('seo.jsonLd.1.offers.availability', 'https://schema.org/InStock'));
});

it('falls back to a confirm specifications prompt when no specs are documented', function () {
    openCategory('pipes-tubes');
    publishProduct('black-steel-pipes');

    $this->get('/ar/products/pipes-tubes/black-steel-pipes')->assertInertia(fn (Assert $page) => $page->where('product.specs', []));
});

it('generates webp sizes for uploaded product images', function () {
    openCategory('pipes-tubes');
    $product = publishProduct('black-steel-pipes');

    $media = $product->getFirstMedia('main');

    expect($media->hasGeneratedConversion('card'))->toBeTrue()
        ->and($media->hasGeneratedConversion('large'))->toBeTrue()
        ->and($product->imageFor('main', 'ar')['srcset'])->toContain('720w');
});

it('searches across spelling variants and synonyms', function () {
    openCategory('pipes-tubes');
    publishProduct('galvanized-steel-pipes');

    foreach (['مقلفن', 'galvanized', 'مواسير', 'ماسوره'] as $term) {
        $this->get('/ar/search?q='.urlencode($term))->assertInertia(fn (Assert $page) => $page
            ->where('products.0.slug', 'galvanized-steel-pipes')
            ->where('seo.robots', 'noindex, follow'));
    }
});

it('logs searches without personal data', function () {
    $this->get('/ar/search?q='.urlencode('صاج 0791234567 ali@example.com'));

    expect(SearchLog::first()->query)->toBe('صاج')
        ->and(CatalogSearch::anonymize('+962 79 123 4567 مواسير'))->toBe('مواسير');
});

it('paginates the full catalogue with real links', function () {
    $published = [
        'sheets-coils' => ['oiled-steel-coils', 'oiled-steel-sheets', 'black-steel-coils', 'black-steel-sheets-plates', 'galvanized-steel-coils', 'galvanized-steel-sheets', 'oiled-steel-strips'],
        'pipes-tubes' => ['steel-hollow-sections', 'galvanized-hollow-sections', 'black-steel-pipes', 'galvanized-steel-pipes', 'sch40-seamless-steel-pipes'],
        'wire-welding' => ['soft-wire-1mm'],
    ];

    foreach ($published as $category => $products) {
        openCategory($category);
        array_map(fn (string $slug) => publishProduct($slug), $products);
    }

    $this->get('/ar/products')->assertInertia(fn (Assert $page) => $page
        ->component('products/index')
        ->has('products.data', 12)
        ->where('products.lastPage', 2)
        ->where('products.next', url('/ar/products?page=2')));

    $this->get('/ar/products?page=2')->assertInertia(fn (Assert $page) => $page->has('products.data', 1));
});
