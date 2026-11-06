<?php

use App\Domain\Catalog\Enums\Availability;
use App\Domain\Catalog\Enums\ReviewStatus;
use App\Domain\Catalog\Exceptions\PublicationBlocked;
use App\Models\Category;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);
});

function readyProduct(): Product
{
    $product = Product::where('slug', 'galvanized-steel-coils')->first();
    Category::find($product->primary_category_id)->update(['review_status' => ReviewStatus::Published]);
    $product->update(['short_description' => ['ar' => 'وصف مختصر', 'en' => 'Short description']]);
    $product->addMedia(UploadedFile::fake()->image('coil.jpg'))->toMediaCollection('main');

    return $product->fresh();
}

it('lists everything blocking publication of a seeded product', function () {
    $product = Product::where('slug', 'galvanized-steel-coils')->first();

    expect($product->publicationBlockers())->toContain('short_description', 'main_image', 'category_unpublished');
});

it('has no blockers once content, image and category are ready', function () {
    expect(readyProduct()->publicationBlockers())->toBe([]);
});

it('refuses to save a product as published while blocked', function () {
    $product = Product::where('slug', 'galvanized-steel-coils')->first();

    expect(fn () => $product->update(['review_status' => ReviewStatus::Published]))
        ->toThrow(PublicationBlocked::class);

    expect($product->fresh()->review_status)->toBe(ReviewStatus::Pending);
});

it('publishes a ready product and stamps published_at', function () {
    $product = readyProduct();
    $product->update(['review_status' => ReviewStatus::Published]);

    expect($product->fresh()->isPublished())->toBeTrue()
        ->and($product->fresh()->published_at)->not->toBeNull();
});

it('requires the english translation too', function () {
    $product = readyProduct();
    $product->setTranslation('short_description', 'en', '');

    expect($product->publicationBlockers())->toContain('short_description');
});

it('stamps the availability date only when availability actually changes', function () {
    $product = readyProduct();
    expect($product->availability_updated_at)->toBeNull();

    $product->update(['availability' => Availability::InStock]);
    $stamped = $product->fresh()->availability_updated_at;

    $this->travel(1)->day();
    $product->update(['sort' => 99]);

    expect($stamped)->not->toBeNull()
        ->and($product->fresh()->availability_updated_at->equalTo($stamped))->toBeTrue();
});
