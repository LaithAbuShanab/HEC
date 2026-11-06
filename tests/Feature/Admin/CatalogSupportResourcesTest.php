<?php

use App\Domain\Access\Role;
use App\Domain\Catalog\Enums\ReviewStatus;
use App\Filament\Admin\Resources\Categories\Pages\EditCategory;
use App\Filament\Admin\Resources\Categories\Pages\ListCategories;
use App\Filament\Admin\Resources\SaleUnits\Pages\ManageSaleUnits;
use App\Filament\Admin\Resources\SearchSynonyms\Pages\ManageSearchSynonyms;
use App\Filament\Admin\Resources\SpecAttributes\Pages\ManageSpecAttributes;
use App\Models\Category;
use App\Models\Product;
use App\Models\SaleUnit;
use App\Models\SearchSynonym;
use App\Models\SpecAttribute;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $editor = User::factory()->create();
    $editor->assignRole(Role::Editor->value);
    $this->actingAs($editor);
});

it('lists categories with their product counts', function () {
    Livewire::test(ListCategories::class)
        ->assertOk()
        ->assertCountTableRecords(9)
        ->assertTableColumnStateSet('primary_products_count', 9, Category::where('slug', 'sheets-coils')->first());
});

it('edits a category in both languages and publishes it once described', function () {
    $category = Category::where('slug', 'pipes-tubes')->first();

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->assertSchemaStateSet(['name.ar' => 'المواسير والتيوبات', 'name.en' => 'Steel Pipes & Tubes'])
        ->fillForm(['review_status' => ReviewStatus::Published->value])
        ->call('save')
        ->assertNotified(__('admin.publication.blocked'));

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm([
            'description.ar' => 'مواسير حديد سوداء ومجلفنة وتيوبات بمقاطع مختلفة.',
            'description.en' => 'Black and galvanized steel pipes and hollow sections.',
            'review_status' => ReviewStatus::Published->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->fresh()->isPublished())->toBeTrue();
});

it('does not allow a category to be its own parent', function () {
    $category = Category::first();

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->assertFormFieldExists('parent_id', fn ($field) => ! array_key_exists($category->id, $field->getOptions()));
});

it('creates and edits sale units with both names', function () {
    Livewire::test(ManageSaleUnits::class)
        ->callAction(TestAction::make('create'), data: ['name' => ['ar' => 'لوح كامل', 'en' => 'Full plate'], 'key' => 'plate'])
        ->assertHasNoActionErrors();

    $unit = SaleUnit::where('key', 'plate')->first();
    expect($unit->getTranslation('name', 'en'))->toBe('Full plate');

    Livewire::test(ManageSaleUnits::class)
        ->callAction(TestAction::make('edit')->table($unit), data: ['name' => ['ar' => 'لوح', 'en' => 'Plate']])
        ->assertHasNoActionErrors();

    expect($unit->fresh()->getTranslation('name', 'ar'))->toBe('لوح');
});

it('refuses to delete a sale unit that products use', function () {
    $unit = SaleUnit::where('key', 'ton')->first();
    Product::first()->update(['sale_unit_id' => $unit->id]);

    Livewire::test(ManageSaleUnits::class)
        ->callAction(TestAction::make('delete')->table($unit))
        ->assertNotified(__('admin.in_use', ['count' => 1]));

    expect(SaleUnit::whereKey($unit->id)->exists())->toBeTrue();
});

it('deletes an unused sale unit', function () {
    $unit = SaleUnit::where('key', 'carton')->first();

    Livewire::test(ManageSaleUnits::class)->callAction(TestAction::make('delete')->table($unit));

    expect(SaleUnit::whereKey($unit->id)->exists())->toBeFalse();
});

it('manages spec attributes', function () {
    Livewire::test(ManageSpecAttributes::class)
        ->callAction(TestAction::make('create'), data: [
            'name' => ['ar' => 'الوزن', 'en' => 'Weight'],
            'key' => 'weight_kg',
            'unit' => 'kg',
            'type' => 'number',
            'is_filterable' => true,
        ])
        ->assertHasNoActionErrors();

    expect(SpecAttribute::where('key', 'weight_kg')->first()->is_filterable)->toBeTrue();
});

it('requires at least two synonyms per group and refreshes the cache', function () {
    expect(SearchSynonym::groups())->toHaveCount(16);

    Livewire::test(ManageSearchSynonyms::class)
        ->callAction(TestAction::make('create'), data: ['terms' => ['صاج']])
        ->assertHasActionErrors(['terms']);

    Livewire::test(ManageSearchSynonyms::class)
        ->callAction(TestAction::make('create'), data: ['terms' => ['مبروم', 'round bar']])
        ->assertHasNoActionErrors();

    expect(SearchSynonym::groups())->toHaveCount(17);
});

it('keeps sales users out of catalog management', function () {
    $sales = User::factory()->create();
    $sales->assignRole(Role::Sales->value);

    $this->actingAs($sales);

    foreach (['categories', 'sale-units', 'spec-attributes', 'search-synonyms'] as $slug) {
        $this->get("/admin/{$slug}")->assertForbidden();
    }
});
