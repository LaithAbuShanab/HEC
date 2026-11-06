<?php

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Faq;
use App\Models\Guide;
use App\Models\Industry;
use App\Models\Page;
use App\Models\Service;
use App\Settings\CompanySettings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);
});

it('returns 404 for every empty section instead of an empty page', function (string $path) {
    $this->get("/ar/{$path}")->assertNotFound();
})->with(['services', 'industries', 'guides', 'work', 'faq', 'about', 'privacy', 'terms', 'services/metal-cutting', 'guides/request-steel-quotation']);

it('always serves the contact page with confirmed details only', function () {
    $this->get('/ar/contact')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('content/contact')
        ->where('profile.phone', null)
        ->where('seo.jsonLd.0.@type', 'Organization'));
});

it('shows a published service with its facts and sanitized body', function () {
    $service = Service::where('slug', 'metal-cutting')->first();
    $service->update([
        'summary' => ['ar' => 'قص الصاج', 'en' => 'Sheet cutting'],
        'body' => ['ar' => '<p>شرح</p><script>alert(1)</script>', 'en' => '<p>Body</p>'],
        'materials' => ['ar' => 'الصاج الأسود', 'en' => 'Black sheet'],
    ]);
    $service->addMedia(UploadedFile::fake()->image('cut.jpg'))->toMediaCollection('cover');
    $service->update(['review_status' => ReviewStatus::Published]);

    $this->get('/ar/services')->assertOk()->assertInertia(fn (Assert $page) => $page->component('content/listing')->has('items', 1));

    $this->get('/ar/services/metal-cutting')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('content/article')
        ->where('article.body', '<p>شرح</p>')
        ->where('article.facts.0.value', 'الصاج الأسود')
        ->where('seo.jsonLd.0.@type', 'BreadcrumbList'));
});

it('publishes guides with article structured data and the reviewer', function () {
    Guide::where('slug', 'request-steel-quotation')->first()->update([
        'excerpt' => ['ar' => 'مقدمة', 'en' => 'Intro'],
        'body' => ['ar' => '<p>محتوى</p>', 'en' => '<p>Body</p>'],
        'technical_reviewer' => 'م. مراجع',
        'technically_reviewed_at' => '2026-11-01',
        'review_status' => ReviewStatus::Published,
    ]);

    $this->get('/en/guides/request-steel-quotation')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('seo.type', 'article')
        ->where('seo.jsonLd.1.@type', 'Article')
        ->where('article.meta.reviewer', 'م. مراجع'));
});

it('groups published faqs and serves published static pages', function () {
    Faq::create(['question' => ['ar' => 'س', 'en' => 'Q'], 'answer' => ['ar' => 'ج', 'en' => 'A'], 'group' => 'quotes', 'review_status' => ReviewStatus::Published]);
    Page::where('key', 'privacy')->first()->update(['body' => ['ar' => '<p>نص</p>', 'en' => '<p>Text</p>'], 'review_status' => ReviewStatus::Published]);

    $this->get('/ar/faq')->assertOk()->assertInertia(fn (Assert $page) => $page->where('groups.0.key', 'quotes'));
    $this->get('/en/privacy')->assertOk()->assertInertia(fn (Assert $page) => $page->where('page.title', 'Privacy Policy'));
});

it('lists industries without detail pages', function () {
    Industry::where('slug', 'traders')->first()->update(['summary' => ['ar' => 'ملخص', 'en' => 'Summary'], 'review_status' => ReviewStatus::Published]);

    $this->get('/en/industries')->assertOk()->assertInertia(fn (Assert $page) => $page->where('items.0.href', null));
});

it('shows confirmed contact channels and opening hours', function () {
    $settings = app(CompanySettings::class);
    $settings->whatsapp_confirmed = true;
    $settings->working_hours = [['days' => ['saturday'], 'opens' => '08:00', 'closes' => '16:00']];
    $settings->working_hours_confirmed = true;
    $settings->save();

    $this->get('/ar/contact')->assertInertia(fn (Assert $page) => $page
        ->where('profile.whatsapp.href', 'https://wa.me/962781221617')
        ->where('profile.hours.0.opens', '08:00'));
});
