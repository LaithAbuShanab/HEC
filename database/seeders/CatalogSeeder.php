<?php

namespace Database\Seeders;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\HistoricalSpec;
use App\Models\Product;
use App\Models\SearchSynonym;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CatalogSeeder extends Seeder
{
    public const SOURCE = 'HEC.docx §10 (2026-10-26)';

    public function run(): void
    {
        $data = File::json(database_path('data/catalog.json'), JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data) {
            $categories = $this->seedCategories($data['categories']);
            $products = $this->seedProducts($data['products'], $categories);
            $this->seedMergedVariants($data['products'], $products);
            $this->seedHistoricalSpecs($data['historical_specs'], $products, $categories);
            $this->seedSynonyms($data['search_synonyms']);
        });
    }

    private function seedCategories(array $rows): array
    {
        $categories = [];
        foreach ($rows as $row) {
            $categories[$row['key']] = Category::updateOrCreate(
                ['slug' => $row['key']],
                ['name' => $row['name'], 'sort' => $row['sort'], 'review_status' => ReviewStatus::Pending],
            );
        }

        return $categories;
    }

    private function seedProducts(array $rows, array $categories): array
    {
        $products = [];
        foreach ($rows as $index => $row) {
            if ($row['slug'] === null) {
                continue;
            }

            $category = $categories[$row['category']];
            $product = Product::updateOrCreate(['appendix_no' => $row['no']], [
                'slug' => $row['slug'],
                'primary_category_id' => $category->id,
                'name' => $row['name'],
                'uses' => $row['uses'],
                'review_status' => ReviewStatus::from($row['status']),
                'missing_fields' => $row['missing'],
                'source_reference' => self::SOURCE,
                'sort' => $index + 1,
            ]);
            $product->categories()->syncWithoutDetaching([$category->id => ['sort' => $index + 1]]);
            $products[$row['slug']] = $product;
        }

        return $products;
    }

    private function seedMergedVariants(array $rows, array $products): void
    {
        foreach ($rows as $row) {
            if (! isset($row['merge_into'])) {
                continue;
            }

            foreach (['galvanized-steel-coils', 'galvanized-steel-sheets'] as $slug) {
                $products[$slug]->variants()->updateOrCreate(
                    ['standard' => 'G90'],
                    ['label' => $row['name'], 'sort' => 1],
                );
            }
        }
    }

    private function seedHistoricalSpecs(array $rows, array $products, array $categories): void
    {
        HistoricalSpec::query()->delete();

        foreach ($rows as $row) {
            HistoricalSpec::create([
                'product_id' => $row['product'] ? $products[$row['product']]->id : null,
                'category_id' => isset($row['category']) ? $categories[$row['category']]->id : null,
                'note' => ['ar' => $row['ar'], 'en' => $row['en']],
                'source' => self::SOURCE,
            ]);
        }
    }

    private function seedSynonyms(array $groups): void
    {
        SearchSynonym::query()->delete();

        foreach ($groups as $terms) {
            SearchSynonym::create(['terms' => $terms]);
        }
    }
}
