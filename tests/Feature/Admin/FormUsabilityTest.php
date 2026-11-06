<?php

use App\Domain\Access\Role;
use App\Filament\Admin\Resources\Products\Pages\CreateProduct;
use App\Filament\Admin\Resources\Products\Pages\EditProduct;
use App\Filament\Admin\Resources\Products\ProductResource;
use App\Filament\Admin\Resources\QuoteRequests\QuoteRequestResource;
use App\Filament\Admin\Resources\Services\Pages\CreateService;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->assignRole(Role::Admin->value);
    $this->actingAs($user);
});

it('suggests a slug from the english name when creating', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm(['name.en' => 'Perforated Steel Sheets'])
        ->assertSchemaStateSet(['slug' => 'perforated-steel-sheets']);

    Livewire::test(CreateService::class)
        ->fillForm(['title.en' => 'Laser Cutting'])
        ->assertSchemaStateSet(['slug' => 'laser-cutting']);
});

it('never overwrites a slug the editor typed or an existing record slug', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm(['slug' => 'my-own-slug'])
        ->fillForm(['name.en' => 'Something Else'])
        ->assertSchemaStateSet(['slug' => 'my-own-slug']);

    $product = Product::where('slug', 'chains')->first();

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['name.en' => 'Lifting Chains'])
        ->assertSchemaStateSet(['slug' => 'chains']);
});

it('finds products in global search with arabic spelling variants', function () {
    $titles = ProductResource::getGlobalSearchResults('اسطوانات')->map->title->all();

    expect($titles)->toContain('أسطوانات قص');
});

it('finds quote requests in global search by reference or phone', function () {
    $quote = QuoteRequest::factory()->create(['phone' => '0791234567']);

    expect(QuoteRequestResource::getGlobalSearchResults('0791234567')->count())->toBe(1)
        ->and(QuoteRequestResource::getGlobalSearchResults($quote->fresh()->reference)->count())->toBe(1);
});
