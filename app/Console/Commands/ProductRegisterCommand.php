<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('hec:product-register {--markdown= : Write the register to this path}')]
#[Description('List the 43 appendix items with their location and review status')]
class ProductRegisterCommand extends Command
{
    private const HEADERS = ['#', 'Arabic name', 'English name', 'ID', 'Location', 'Status', 'Missing'];

    public function handle(): int
    {
        $source = File::json(database_path('data/catalog.json'), JSON_THROW_ON_ERROR);
        $products = Product::withTrashed()->whereNotNull('appendix_no')->get()->keyBy('appendix_no');

        $rows = array_map(
            fn (array $item) => isset($item['merge_into']) ? $this->mergedRow($item) : $this->productRow($item, $products->get($item['no'])),
            $source['products'],
        );

        $this->table(self::HEADERS, $rows);

        if ($path = $this->option('markdown')) {
            File::put($path, $this->markdown($rows));
            $this->info("Register written to {$path}");
        }

        $notFound = array_filter($rows, fn (array $row) => $row[4] === 'NOT FOUND');

        return $notFound === [] ? self::SUCCESS : self::FAILURE;
    }

    private function productRow(array $item, ?Product $product): array
    {
        if ($product === null) {
            return [$item['no'], $item['name']['ar'], $item['name']['en'], '-', 'NOT FOUND', '-', ''];
        }

        $categorySlug = Category::withTrashed()->whereKey($product->primary_category_id)->value('slug');

        return [
            $item['no'],
            $item['name']['ar'],
            $item['name']['en'],
            $product->uuid,
            "/products/{$categorySlug}/{$product->slug}/",
            $product->review_status->value,
            implode(', ', $product->missing_fields ?? []),
        ];
    }

    private function mergedRow(array $item): array
    {
        $parents = Product::whereHas('variants', fn ($query) => $query->where('standard', 'G90'))->pluck('slug');

        return [
            $item['no'],
            $item['name']['ar'],
            $item['name']['en'],
            '-',
            $parents->isEmpty() ? 'NOT FOUND' : 'G90 variant of '.$parents->implode(', '),
            $item['status'],
            implode(', ', $item['missing']),
        ];
    }

    private function markdown(array $rows): string
    {
        $lines = [
            '# HEC product register',
            '',
            'Generated '.now()->toDateTimeString(),
            '',
            '| '.implode(' | ', self::HEADERS).' |',
            '|'.str_repeat('---|', count(self::HEADERS)),
        ];

        foreach ($rows as $row) {
            $lines[] = '| '.implode(' | ', array_map(fn ($cell) => str_replace('|', '/', (string) $cell), $row)).' |';
        }

        return implode("\n", $lines)."\n";
    }
}
