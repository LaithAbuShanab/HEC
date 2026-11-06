<?php

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Domain\Site\SiteNavigation;
use App\Models\Category;
use App\Models\Industry;
use App\Models\Service;
use Database\Seeders\DatabaseSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

function publishCategory(string $slug): Category
{
    $category = Category::where('slug', $slug)->first();
    $category->update(['description' => ['ar' => 'وصف', 'en' => 'Description'], 'review_status' => ReviewStatus::Published]);

    return $category;
}

it('hides sections that have no published content', function () {
    $nav = app(SiteNavigation::class)->forLocale('ar');

    expect($nav['categories'])->toBe([])
        ->and($nav['sections'])->toBe(['services' => false, 'industries' => false, 'guides' => false, 'work' => false, 'faq' => false, 'about' => false]);
});

it('lists published categories with their published product counts', function () {
    publishCategory('pipes-tubes');

    $nav = app(SiteNavigation::class)->forLocale('en');

    expect($nav['categories'])->toHaveCount(1)
        ->and($nav['categories'][0])->toMatchArray(['slug' => 'pipes-tubes', 'name' => 'Steel Pipes & Tubes', 'products' => 0]);
});

it('refreshes as soon as content is published', function () {
    expect(app(SiteNavigation::class)->forLocale('ar')['sections']['industries'])->toBeFalse();

    Industry::where('slug', 'traders')->first()->update([
        'summary' => ['ar' => 'ملخص', 'en' => 'Summary'],
        'review_status' => ReviewStatus::Published,
    ]);

    expect(app(SiteNavigation::class)->forLocale('ar')['sections']['industries'])->toBeTrue();
});

it('shares navigation, interface strings and company details with every public page', function () {
    publishCategory('sheets-coils');

    $this->get('/ar')->assertInertia(fn (Assert $page) => $page
        ->where('nav.categories.0.slug', 'sheets-coils')
        ->where('ui.actions.request_quote', 'اطلب عرض سعر')
        ->has('company'));

    $this->get('/en')->assertInertia(fn (Assert $page) => $page->where('ui.actions.request_quote', 'Request a quote'));
});

it('does not leak unpublished services', function () {
    Service::query()->update(['review_status' => ReviewStatus::Pending]);

    expect(app(SiteNavigation::class)->forLocale('ar')['sections']['services'])->toBeFalse();
});
