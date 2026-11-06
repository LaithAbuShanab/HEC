<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Filament\Admin\Resources\Products\ProductResource;
use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Models\Product;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ActionGroup::make([
                ImportAction::make()
                    ->label(__('admin.exchange.import'))
                    ->importer(ProductImporter::class)
                    ->modalDescription(__('admin.exchange.blank_keeps'))
                    ->authorize(fn () => auth()->user()?->can('create', Product::class) ?? false),
                ExportAction::make()
                    ->label(__('admin.exchange.export'))
                    ->exporter(ProductExporter::class)
                    ->authorize(fn () => auth()->user()?->can('viewAny', Product::class) ?? false),
            ])->button()->label(__('admin.exchange.import').' / '.__('admin.exchange.export'))->icon('heroicon-o-arrows-right-left')->color('gray'),
        ];
    }
}
