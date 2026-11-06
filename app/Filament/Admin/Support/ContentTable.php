<?php

namespace App\Filament\Admin\Support;

use App\Domain\Catalog\Enums\ReviewStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

final class ContentTable
{
    public static function configure(Table $table, ?string $imageCollection = null, bool $reorderable = true, array $extraColumns = []): Table
    {
        $columns = [];

        if ($imageCollection !== null) {
            $columns[] = SpatieMediaLibraryImageColumn::make($imageCollection)->label('')->collection($imageCollection)->square()->imageSize(44)->limit(1);
        }

        $table = $table
            ->columns([
                ...$columns,
                Columns::translated('title'),
                TextColumn::make('slug')->label(__('admin.common.slug'))->color('gray')->toggleable()->formatStateUsing(fn (?string $state) => Display::ltr($state)),
                ...$extraColumns,
                Columns::reviewStatus(),
                Columns::updatedAt(),
            ])
            ->filters([
                SelectFilter::make('review_status')->label(__('admin.common.review_status'))->options(ReviewStatus::options()),
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

        return $reorderable ? $table->reorderable('sort')->defaultSort('sort') : $table->defaultSort('updated_at', 'desc');
    }
}
