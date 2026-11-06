<?php

use App\Domain\Access\Role;
use App\Domain\Catalog\Enums\Availability;
use App\Domain\Catalog\Enums\ReviewStatus;
use App\Filament\Admin\Resources\Products\Pages\CreateProduct;
use App\Filament\Admin\Resources\Products\Pages\EditProduct;
use App\Filament\Admin\Resources\Products\Pages\ListProducts;
use App\Filament\Admin\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->editor = User::factory()->create();
    $this->editor->assignRole(Role::Editor->value);
    $this->actingAs($this->editor);
});

it('lists all seeded products for editors', function () {
    Livewire::test(ListProducts::class)
        ->assertOk()
        ->assertCanSeeTableRecords(Product::limit(10)->get())
        ->assertCountTableRecords(42);
});

it('blocks the product list for sales', function () {
    $sales = User::factory()->create();
    $sales->assignRole(Role::Sales->value);

    $this->actingAs($sales)->get(route('filament.admin.resources.products.index'))->assertForbidden();
});

it('finds products with arabic spelling variants in the table search', function () {
    Livewire::test(ListProducts::class)
        ->searchTable('مجلفن')
        ->assertCanSeeTableRecords(Product::where('slug', 'galvanized-steel-pipes')->get())
        ->assertCanNotSeeTableRecords(Product::where('slug', 'black-steel-pipes')->get())
        ->searchTable('اسطوانات')
        ->assertCanSeeTableRecords(Product::where('slug', 'cut-off-wheels')->get());
});

it('fills both languages and saves them side by side', function () {
    $product = Product::where('slug', 'steel-angles')->first();

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertSchemaStateSet([
            'name.ar' => 'زوايا حديد',
            'name.en' => 'Steel Angles',
        ])
        ->fillForm([
            'short_description.ar' => 'زوايا حديد بمقاسات مختلفة',
            'short_description.en' => 'Steel angles in several sizes',
            'name.en' => 'Steel Angle Bars',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $product->refresh();

    expect($product->getTranslation('name', 'en'))->toBe('Steel Angle Bars')
        ->and($product->getTranslation('name', 'ar'))->toBe('زوايا حديد')
        ->and($product->getTranslation('short_description', 'en'))->toBe('Steel angles in several sizes');
});

it('clears a translation when the field is emptied', function () {
    $product = Product::where('slug', 'steel-angles')->first();
    $product->update(['short_description' => ['ar' => 'وصف', 'en' => 'Description']]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['short_description.en' => ''])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->fresh()->getTranslation('short_description', 'en', false))->toBe('');
});

it('refuses to publish an incomplete product and keeps it unpublished', function () {
    $product = Product::where('slug', 'steel-angles')->first();

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['review_status' => ReviewStatus::Published->value])
        ->call('save')
        ->assertNotified(__('admin.publication.blocked'));

    expect($product->fresh()->review_status)->toBe(ReviewStatus::Pending);
});

it('validates slugs', function (string $slug) {
    $product = Product::where('slug', 'steel-angles')->first();

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['slug' => $slug])
        ->call('save')
        ->assertHasFormErrors(['slug']);
})->with(['Steel Angles', 'زوايا', 'steel_angles', 'chains']);

it('creates a product as draft with both names required', function () {
    $category = Category::first();

    Livewire::test(CreateProduct::class)
        ->fillForm(['name.ar' => 'منتج', 'slug' => 'new-product', 'primary_category_id' => $category->id])
        ->call('create')
        ->assertHasFormErrors(['name.en' => 'required']);

    Livewire::test(CreateProduct::class)
        ->fillForm(['name.ar' => 'منتج', 'name.en' => 'Product', 'slug' => 'new-product', 'primary_category_id' => $category->id])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Product::where('slug', 'new-product')->first()->review_status)->toBe(ReviewStatus::Draft);
});

it('updates availability in bulk and stamps the date', function () {
    $products = Product::whereIn('slug', ['steel-angles', 'chains'])->get();

    Livewire::test(ListProducts::class)
        ->selectTableRecords($products->modelKeys())
        ->callAction(TestAction::make('updateAvailability')->table()->bulk(), data: ['availability' => Availability::InStock->value])
        ->assertHasNoActionErrors();

    foreach ($products as $product) {
        $product->refresh();
        expect($product->availability)->toBe(Availability::InStock)
            ->and($product->availability_updated_at)->not->toBeNull();
    }

    expect(Product::where('availability', Availability::InStock)->count())->toBe(2);
});

it('reports products that could not be published in bulk', function () {
    $products = Product::whereIn('slug', ['steel-angles', 'chains'])->get();

    Livewire::test(ListProducts::class)
        ->selectTableRecords($products->modelKeys())
        ->callAction(TestAction::make('changeReviewStatus')->table()->bulk(), data: ['review_status' => ReviewStatus::Published->value])
        ->assertNotified(__('admin.products.actions.bulk_publish_partial', ['published' => 0, 'blocked' => 2]));

    expect(Product::published()->count())->toBe(0);
});

it('manages variants with bilingual labels', function () {
    $product = Product::where('slug', 'galvanized-steel-pipes')->first();

    Livewire::test(VariantsRelationManager::class, ['ownerRecord' => $product, 'pageClass' => EditProduct::class])
        ->callAction(TestAction::make('create')->table(), data: [
            'label' => ['ar' => 'ماسورة 2 إنش', 'en' => '2 inch pipe'],
            'nominal_size' => '2"',
            'thickness_mm' => 1.5,
        ])
        ->assertHasNoActionErrors();

    $variant = $product->variants()->first();

    expect($variant->labelFor('en'))->toBe('2 inch pipe')
        ->and((float) $variant->thickness_mm)->toBe(1.5)
        ->and($variant->nominal_size)->toBe('2"');
});
