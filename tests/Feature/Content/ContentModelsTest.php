<?php

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Domain\Catalog\Exceptions\PublicationBlocked;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Guide;
use App\Models\Industry;
use App\Models\Page;
use App\Models\Product;
use App\Models\Service;
use App\Models\WorkItem;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\SaleUnitSeeder;
use Database\Seeders\SpecAttributeSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => $this->seed([SaleUnitSeeder::class, SpecAttributeSeeder::class, CatalogSeeder::class]));

it('only exposes published content through the published scope', function () {
    Service::create(['slug' => 'metal-cutting-bending', 'title' => ['ar' => 'قص وثني', 'en' => 'Cutting and bending']]);
    Industry::create(['slug' => 'workshops', 'title' => ['ar' => 'ورش', 'en' => 'Workshops'], 'summary' => ['ar' => 'ملخص', 'en' => 'Summary'], 'review_status' => ReviewStatus::Published]);
    Industry::create(['slug' => 'traders', 'title' => ['ar' => 'التجار', 'en' => 'Traders']]);

    expect(Industry::published()->pluck('slug')->all())->toBe(['workshops']);
});

it('stamps published_at the first time a record is published', function () {
    $guide = Guide::create([
        'slug' => 'request-steel-quotation',
        'title' => ['ar' => 'دليل', 'en' => 'Guide'],
        'excerpt' => ['ar' => 'مقدمة', 'en' => 'Intro'],
        'body' => ['ar' => 'محتوى', 'en' => 'Body'],
        'technical_reviewer' => 'Eng. Reviewer',
        'technically_reviewed_at' => now(),
    ]);
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

it('requires a technical review before a guide is published', function () {
    $guide = Guide::create([
        'slug' => 'compare-sheets',
        'title' => ['ar' => 'الفروق', 'en' => 'Differences'],
        'excerpt' => ['ar' => 'مقدمة', 'en' => 'Intro'],
        'body' => ['ar' => 'محتوى', 'en' => 'Body'],
    ]);

    expect(fn () => $guide->update(['review_status' => ReviewStatus::Published]))
        ->toThrow(PublicationBlocked::class, 'technical_review');
});

it('requires a real photo before a service is published', function () {
    Storage::fake('public');
    $service = Service::create([
        'slug' => 'metal-cutting-bending',
        'title' => ['ar' => 'قص وثني', 'en' => 'Cutting and bending'],
        'summary' => ['ar' => 'ملخص', 'en' => 'Summary'],
        'body' => ['ar' => 'شرح', 'en' => 'Body'],
    ]);

    expect($service->publicationBlockers())->toBe(['cover_image']);

    $service->addMedia(UploadedFile::fake()->image('cutting.jpg'))->toMediaCollection('cover');

    expect($service->publicationBlockers())->toBe([]);
});

it('requires at least one real photo before a work item is published', function () {
    $work = WorkItem::create([
        'slug' => 'coil-loading',
        'title' => ['ar' => 'تحميل رولات', 'en' => 'Coil loading'],
        'summary' => ['ar' => 'ملخص', 'en' => 'Summary'],
    ]);

    expect($work->publicationBlockers())->toBe(['gallery']);
});

it('requires both languages for faqs and pages', function () {
    $faq = Faq::create(['question' => ['ar' => 'سؤال'], 'answer' => ['ar' => 'جواب']]);
    $page = Page::create(['key' => 'terms', 'title' => ['ar' => 'الشروط', 'en' => 'Terms']]);

    expect($faq->publicationBlockers())->toBe(['question', 'answer'])
        ->and($page->publicationBlockers())->toBe(['body']);
});
