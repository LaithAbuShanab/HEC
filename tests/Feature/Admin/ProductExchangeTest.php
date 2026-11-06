<?php

use App\Domain\Access\Role;
use App\Domain\Catalog\Enums\Availability;
use App\Domain\Catalog\Enums\ReviewStatus;
use App\Domain\Catalog\Exchange\ProductSheet;
use App\Filament\Admin\Resources\Products\Pages\ListProducts;
use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\Models\Import;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->editor = User::factory()->create();
    $this->editor->assignRole(Role::Editor->value);
    $this->actingAs($this->editor);
});

function importRow(array $row): void
{
    $import = Import::create([
        'file_name' => 'products.csv',
        'file_path' => 'products.csv',
        'importer' => ProductImporter::class,
        'total_rows' => 1,
        'user_id' => auth()->id(),
    ]);

    $columnMap = array_combine(array_keys($row), array_keys($row));

    (new ProductImporter($import, $columnMap, []))($row);
}

it('updates only the cells that are filled', function () {
    $product = Product::where('slug', 'steel-angles')->first();

    importRow([
        'slug' => 'steel-angles',
        'name_en' => 'Steel Angle Bars',
        'name_ar' => '',
        'availability' => 'in_stock',
        'sale_unit' => 'piece',
        'missing_fields' => 'sizes | images',
    ]);

    $product->refresh();

    expect($product->getTranslation('name', 'en'))->toBe('Steel Angle Bars')
        ->and($product->getTranslation('name', 'ar'))->toBe('زوايا حديد')
        ->and($product->availability)->toBe(Availability::InStock)
        ->and($product->availability_updated_at)->not->toBeNull()
        ->and($product->saleUnit->key)->toBe('piece')
        ->and($product->missing_fields)->toBe(['sizes', 'images'])
        ->and($product->appendix_no)->toBe('13');
});

it('creates new products as drafts in their category', function () {
    importRow([
        'slug' => 'perforated-sheets',
        'name_ar' => 'صاج مثقب',
        'name_en' => 'Perforated Sheets',
        'category' => 'sheets-coils',
    ]);

    $product = Product::where('slug', 'perforated-sheets')->first();

    expect($product->review_status)->toBe(ReviewStatus::Draft)
        ->and($product->appendix_no)->toBeNull()
        ->and($product->categories()->pluck('slug')->all())->toBe(['sheets-coils']);
});

it('refuses new products without both names and a category', function () {
    expect(fn () => importRow(['slug' => 'half-product', 'name_ar' => 'منتج']))
        ->toThrow(RowImportFailedException::class);

    expect(Product::where('slug', 'half-product')->exists())->toBeFalse();
});

it('cannot publish through import while publication rules fail', function () {
    expect(fn () => importRow(['slug' => 'chains', 'review_status' => 'published']))
        ->toThrow(RowImportFailedException::class);

    expect(Product::where('slug', 'chains')->first()->review_status)->toBe(ReviewStatus::Pending);
});

it('rejects invalid values', function (array $row) {
    expect(fn () => importRow(array_merge(['slug' => 'chains'], $row)))->toThrow(ValidationException::class);
})->with([
    'unknown category' => [['category' => 'no-such-category']],
    'unknown sale unit' => [['sale_unit' => 'barrel']],
    'bad availability' => [['availability' => 'plenty']],
    'zero price' => [['price' => '0']],
    'bad slug' => [['slug' => 'Bad Slug']],
]);

it('exports every column the importer understands', function () {
    $importable = collect(ProductImporter::getColumns())->map->getName()->all();
    $exported = collect(ProductExporter::getColumns())->map->getName()->all();

    expect($exported)->toBe(ProductSheet::headers())
        ->and(array_diff($importable, $exported))->toBe([]);
});

it('exports the catalog to private storage', function () {
    Storage::fake('local');

    Livewire::test(ListProducts::class)
        ->callAction(TestAction::make('export'))
        ->assertHasNoActionErrors();

    $export = Export::latest('id')->first();

    expect($export->successful_rows)->toBe(42)
        ->and($export->file_disk)->toBe('local');
});

it('does not offer import or export to sales', function () {
    $sales = User::factory()->create();
    $sales->assignRole(Role::Sales->value);

    $this->actingAs($sales)->get('/admin/products')->assertForbidden();
});
