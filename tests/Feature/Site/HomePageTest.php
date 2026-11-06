<?php

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);
});

function publishedCategory(string $slug): Category
{
    $category = Category::where('slug', $slug)->first();
    $category->update(['description' => ['ar' => 'وصف التصنيف', 'en' => 'Category description'], 'review_status' => ReviewStatus::Published]);

    return $category;
}

it('renders an empty but complete home page while nothing is published', function () {
    $this->get('/ar')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('home')
        ->where('categories', [])
        ->where('featured', [])
        ->where('services', [])
        ->where('faqs', []));
});

it('shows only published categories with arabic plural product counts', function () {
    $category = publishedCategory('pipes-tubes');
    $product = Product::where('slug', 'galvanized-steel-pipes')->first();
    $product->update(['short_description' => ['ar' => 'وصف', 'en' => 'Summary']]);
    $product->addMedia(UploadedFile::fake()->image('pipe.jpg'))->toMediaCollection('main');
    $product->update(['review_status' => ReviewStatus::Published]);

    $this->get('/ar')->assertInertia(fn (Assert $page) => $page
        ->has('categories', 1)
        ->where('categories.0.slug', $category->slug)
        ->where('categories.0.products', 1)
        ->where('categories.0.products_label', 'منتج واحد'));

    $this->get('/en')->assertInertia(fn (Assert $page) => $page
        ->where('categories.0.name', 'Steel Pipes & Tubes')
        ->where('categories.0.products_label', '1 product'));
});

it('features only published products marked as featured', function () {
    publishedCategory('pipes-tubes');
    $product = Product::where('slug', 'black-steel-pipes')->first();
    $product->update(['short_description' => ['ar' => 'وصف', 'en' => 'Summary'], 'is_featured' => true]);
    $product->addMedia(UploadedFile::fake()->image('pipe.jpg'))->toMediaCollection('main');

    $this->get('/ar')->assertInertia(fn (Assert $page) => $page->where('featured', []));

    $product->update(['review_status' => ReviewStatus::Published]);

    $this->get('/ar')->assertInertia(fn (Assert $page) => $page
        ->has('featured', 1)
        ->where('featured.0.category', 'pipes-tubes')
        ->where('featured.0.availability', 'يحتاج تأكيد'));
});

it('lists only published faqs marked for the home page', function () {
    Faq::create(['question' => ['ar' => 'س', 'en' => 'Q'], 'answer' => ['ar' => 'ج', 'en' => 'A'], 'show_on_home' => true, 'review_status' => ReviewStatus::Published]);
    Faq::create(['question' => ['ar' => 'مخفي', 'en' => 'Hidden'], 'answer' => ['ar' => 'ج', 'en' => 'A'], 'show_on_home' => false, 'review_status' => ReviewStatus::Published]);
    Faq::create(['question' => ['ar' => 'مسودة', 'en' => 'Draft'], 'answer' => ['ar' => 'ج', 'en' => 'A'], 'show_on_home' => true]);

    $this->get('/en')->assertInertia(fn (Assert $page) => $page->has('faqs', 1)->where('faqs.0.question', 'Q'));
});

it('never exposes unconfirmed contact details on the home page', function () {
    $this->get('/ar')->assertInertia(fn (Assert $page) => $page
        ->where('company.phone', null)
        ->where('company.whatsapp', null));
});
