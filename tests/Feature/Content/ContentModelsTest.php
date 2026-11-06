<?php

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Guide;
use App\Models\Page;
use App\Models\Product;
use App\Models\Service;
use Database\Seeders\DatabaseSeeder;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('only exposes published content through the published scope', function () {
    Service::create(['slug' => 'metal-cutting-bending', 'title' => ['ar' => 'قص وثني', 'en' => 'Cutting and bending']]);
    Service::create(['slug' => 'site-delivery', 'title' => ['ar' => 'التوريد للموقع', 'en' => 'Site delivery'], 'review_status' => ReviewStatus::Published]);

    expect(Service::published()->pluck('slug')->all())->toBe(['site-delivery']);
});

it('stamps published_at the first time a record is published', function () {
    $guide = Guide::create(['slug' => 'request-steel-quotation', 'title' => ['ar' => 'دليل', 'en' => 'Guide']]);
    expect($guide->published_at)->toBeNull();

    $guide->update(['review_status' => ReviewStatus::Published]);
    $firstPublishedAt = $guide->fresh()->published_at;

    $guide->update(['review_status' => ReviewStatus::Pending]);
    $guide->update(['review_status' => ReviewStatus::Published]);

    expect($firstPublishedAt)->not->toBeNull()
        ->and($guide->fresh()->published_at->equalTo($firstPublishedAt))->toBeTrue();
});

it('does not stamp published_at on models without the column', function () {
    $category = Category::first();
    $category->update([
        'description' => ['ar' => 'وصف', 'en' => 'Description'],
        'review_status' => ReviewStatus::Published,
    ]);

    expect($category->fresh()->isPublished())->toBeTrue();
});

it('links services and guides to products and categories', function () {
    $product = Product::where('slug', 'galvanized-steel-coils')->first();
    $service = Service::create(['slug' => 'metal-cutting-bending', 'title' => ['ar' => 'قص', 'en' => 'Cutting']]);
    $guide = Guide::create(['slug' => 'black-oiled-galvanized', 'title' => ['ar' => 'الفروق', 'en' => 'Differences']]);

    $service->products()->attach($product);
    $guide->products()->attach($product);
    $guide->categories()->attach($product->primary_category_id);

    expect($service->products()->first()->is($product))->toBeTrue()
        ->and($guide->categories()->first()->slug)->toBe('sheets-coils');
});

it('stores faq and page translations', function () {
    $faq = Faq::create(['question' => ['ar' => 'سؤال', 'en' => 'Question'], 'answer' => ['ar' => 'جواب', 'en' => 'Answer']]);
    $page = Page::create(['key' => 'privacy', 'title' => ['ar' => 'الخصوصية', 'en' => 'Privacy']]);

    app()->setLocale('en');

    expect($faq->question)->toBe('Question')
        ->and($page->title)->toBe('Privacy')
        ->and($page->isPublished())->toBeFalse();
});
