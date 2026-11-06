<?php

namespace App\Domain\Reporting;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class CatalogHealth
{
    public function summary(): array
    {
        $counts = Product::query()->selectRaw('review_status, count(*) as total')->groupBy('review_status')->pluck('total', 'review_status');

        return [
            'total' => (int) $counts->sum(),
            'published' => (int) ($counts[ReviewStatus::Published->value] ?? 0),
            'pending' => (int) ($counts[ReviewStatus::Pending->value] ?? 0),
            'draft' => (int) ($counts[ReviewStatus::Draft->value] ?? 0),
            'missing_main_image' => Product::query()->whereDoesntHave('media', fn (Builder $media) => $media->where('collection_name', 'main'))->count(),
            'with_missing_fields' => Product::query()->whereNotNull('missing_fields')->where('missing_fields', '!=', '[]')->count(),
        ];
    }

    public function needingAttention(int $limit = 10): Collection
    {
        return Product::query()
            ->where('review_status', '!=', ReviewStatus::Published)
            ->orderByRaw('case when review_status = ? then 0 else 1 end', [ReviewStatus::Pending->value])
            ->orderBy('sort')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->getTranslation('name', 'ar'),
                'status' => $product->review_status,
                'blockers' => $product->publicationBlockers(),
                'missing' => $product->missing_fields ?? [],
            ]);
    }
}
