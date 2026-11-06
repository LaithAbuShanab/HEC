<?php

namespace App\Domain\Catalog;

use App\Domain\Catalog\Enums\Availability;
use App\Domain\Catalog\Enums\ProductCondition;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class CatalogFilters
{
    public const KEYS = ['availability', 'condition', 'brand', 'origin', 'thickness', 'diameter', 'nominal_size', 'width'];

    private array $selected;

    public function __construct(private readonly string $locale, array $query)
    {
        $this->selected = [];

        foreach (self::KEYS as $key) {
            $values = $query[$key] ?? [];
            $values = is_array($values) ? $values : explode(',', (string) $values);
            $values = array_values(array_unique(array_filter(array_map(fn ($value) => trim((string) $value), $values), fn (string $value) => $value !== '')));

            if ($values !== []) {
                $this->selected[$key] = array_slice($values, 0, 20);
            }
        }
    }

    public static function fromRequest(Request $request, string $locale): self
    {
        return new self($locale, $request->query());
    }

    public function isFiltered(): bool
    {
        return $this->selected !== [];
    }

    public function selected(): array
    {
        return $this->selected;
    }

    public function apply(Collection $products): Collection
    {
        return $products->filter(function (Product $product) {
            foreach ($this->selected as $key => $values) {
                if (array_intersect($this->valuesOf($product, $key), $values) === []) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    public function facets(Collection $products): array
    {
        $facets = [];

        foreach (self::KEYS as $key) {
            $counts = [];

            foreach ($products as $product) {
                foreach (array_unique($this->valuesOf($product, $key)) as $value) {
                    $counts[$value] = ($counts[$value] ?? 0) + 1;
                }
            }

            if (count($counts) < 2 && ! isset($this->selected[$key])) {
                continue;
            }

            $options = [];
            foreach ($counts as $value => $count) {
                $options[] = [
                    'value' => (string) $value,
                    'label' => $this->label($key, (string) $value),
                    'count' => $count,
                    'selected' => in_array((string) $value, $this->selected[$key] ?? [], true),
                ];
            }

            usort($options, fn (array $a, array $b) => is_numeric($a['value']) && is_numeric($b['value'])
                ? (float) $a['value'] <=> (float) $b['value']
                : strcmp($a['label'], $b['label']));

            $facets[] = ['key' => $key, 'label' => __("site.filters.{$key}", locale: $this->locale), 'options' => $options];
        }

        return $facets;
    }

    private function valuesOf(Product $product, string $key): array
    {
        $variants = $product->relationLoaded('variants') ? $product->variants : collect();
        $number = fn ($value) => $value === null ? null : rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');

        $values = match ($key) {
            'availability' => [$product->availability->value, ...$variants->map(fn (ProductVariant $variant) => $variant->availability?->value)->all()],
            'condition' => [$product->condition === ProductCondition::Unconfirmed ? null : $product->condition->value],
            'brand' => [$product->brand, $product->model],
            'origin' => [$product->getTranslation('origin', $this->locale, false) ?: null],
            'thickness' => $variants->map(fn (ProductVariant $variant) => $number($variant->thickness_mm))->all(),
            'diameter' => $variants->map(fn (ProductVariant $variant) => $number($variant->diameter_mm))->all(),
            'width' => $variants->map(fn (ProductVariant $variant) => $number($variant->width_mm))->all(),
            'nominal_size' => $variants->map(fn (ProductVariant $variant) => $variant->nominal_size)->all(),
            default => [],
        };

        return array_values(array_filter(array_map(fn ($value) => $value === null ? null : (string) $value, $values), fn ($value) => filled($value)));
    }

    private function label(string $key, string $value): string
    {
        return match ($key) {
            'availability' => Availability::tryFrom($value)?->label($this->locale) ?? $value,
            'condition' => ProductCondition::tryFrom($value)?->label($this->locale) ?? $value,
            default => $value,
        };
    }
}
