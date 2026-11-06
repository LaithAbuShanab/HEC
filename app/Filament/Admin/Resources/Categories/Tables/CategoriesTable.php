<?php

namespace App\Filament\Admin\Resources\Categories\Tables;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Filament\Admin\Support\Columns;
use App\Filament\Admin\Support\Display;
use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->label('')->collection('cover')->square()->imageSize(44),
                TextColumn::make('name')
                    ->label(__('admin.categories.fields.name'))
                    ->state(fn (Category $record) => $record->getTranslation('name', 'ar'))
                    ->description(fn (Category $record) => $record->getTranslation('name', 'en', false)),
                TextColumn::make('slug')->label(__('admin.common.slug'))->color('gray')->formatStateUsing(fn (?string $state) => Display::ltr($state)),
                TextColumn::make('primary_products_count')->label(__('admin.categories.fields.products_count'))->badge(),
                Columns::reviewStatus(),
            ])
            ->filters([
                SelectFilter::make('review_status')->label(__('admin.common.review_status'))->options(ReviewStatus::options('ar')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
