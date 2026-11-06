<?php

namespace App\Filament\Admin\Widgets;

use App\Domain\Access\Role;
use App\Domain\Catalog\Enums\ReviewStatus;
use App\Filament\Admin\Resources\Products\ProductResource;
use App\Filament\Admin\Support\Columns;
use App\Filament\Admin\Widgets\Concerns\VisibleToRoles;
use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ProductsNeedingAttentionTable extends TableWidget
{
    use VisibleToRoles;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected static function roles(): array
    {
        return [Role::Editor];
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('admin.dashboard.catalog.attention'))
            ->query(Product::query()
                ->where('review_status', '!=', ReviewStatus::Published)
                ->orderByRaw('case when review_status = ? then 0 else 1 end', [ReviewStatus::Pending->value])
                ->orderBy('sort'))
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10, 25])
            ->recordUrl(fn (Product $record) => ProductResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('appendix_no')->label('#')->placeholder('—'),
                Columns::translated('name', __('admin.products.fields.name')),
                Columns::reviewStatus(),
                TextColumn::make('blockers')
                    ->label(__('admin.dashboard.catalog.blockers'))
                    ->state(fn (Product $record) => array_map(fn (string $key) => __("admin.publication.blockers.{$key}"), $record->publicationBlockers()))
                    ->badge()
                    ->color('danger'),
                TextColumn::make('missing_fields')
                    ->label(__('admin.dashboard.catalog.missing'))
                    ->badge()
                    ->color('warning')
                    ->limitList(3),
            ]);
    }
}
