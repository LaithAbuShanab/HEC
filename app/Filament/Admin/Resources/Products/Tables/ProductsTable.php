<?php

namespace App\Filament\Admin\Resources\Products\Tables;

use App\Domain\Catalog\Enums\Availability;
use App\Domain\Catalog\Enums\ReviewStatus;
use App\Domain\Catalog\Exceptions\PublicationBlocked;
use App\Domain\Catalog\Support\SearchNormalizer;
use App\Filament\Admin\Support\Options;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                SpatieMediaLibraryImageColumn::make('main')
                    ->label('')
                    ->collection('main')
                    ->square()
                    ->imageSize(44),
                TextColumn::make('appendix_no')
                    ->label('#')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('name')
                    ->label(__('admin.products.fields.name'))
                    ->state(fn (Product $record) => $record->getTranslation('name', 'ar'))
                    ->description(fn (Product $record) => $record->getTranslation('name', 'en', false))
                    ->searchable(query: fn (Builder $query, string $search) => self::search($query, $search))
                    ->wrap(),
                TextColumn::make('primaryCategory.name')
                    ->label(__('admin.products.fields.primary_category'))
                    ->state(fn (Product $record) => $record->primaryCategory instanceof Category ? $record->primaryCategory->getTranslation('name', 'ar') : null)
                    ->toggleable(),
                TextColumn::make('review_status')
                    ->label(__('admin.common.review_status'))
                    ->badge()
                    ->formatStateUsing(fn (ReviewStatus $state) => $state->label('ar'))
                    ->color(fn (ReviewStatus $state) => match ($state) {
                        ReviewStatus::Draft => 'gray',
                        ReviewStatus::Pending => 'warning',
                        ReviewStatus::Published => 'success',
                    }),
                TextColumn::make('availability')
                    ->label(__('admin.products.fields.availability'))
                    ->badge()
                    ->formatStateUsing(fn (Availability $state) => $state->label('ar'))
                    ->color(fn (Availability $state) => match ($state) {
                        Availability::NeedsConfirmation => 'gray',
                        Availability::InStock => 'success',
                        Availability::OnOrder => 'info',
                        Availability::TemporarilyUnavailable => 'danger',
                    }),
                TextColumn::make('availability_updated_at')
                    ->label(__('admin.products.fields.availability_updated_at'))
                    ->since()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('missing_fields')
                    ->label(__('admin.products.fields.missing_fields'))
                    ->state(fn (Product $record) => count($record->missing_fields ?? []))
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'warning' : 'success'),
                IconColumn::make('is_featured')
                    ->label(__('admin.products.fields.is_featured'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('review_status')
                    ->label(__('admin.common.review_status'))
                    ->options(ReviewStatus::options('ar')),
                SelectFilter::make('availability')
                    ->label(__('admin.products.fields.availability'))
                    ->options(Availability::options('ar')),
                SelectFilter::make('primary_category_id')
                    ->label(__('admin.products.fields.primary_category'))
                    ->options(Options::categories()),
                TernaryFilter::make('missing_main_image')
                    ->label(__('admin.products.filters.missing_main_image'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereDoesntHave('media', fn (Builder $media) => $media->where('collection_name', 'main')),
                        false: fn (Builder $query) => $query->whereHas('media', fn (Builder $media) => $media->where('collection_name', 'main')),
                    ),
                TernaryFilter::make('has_missing_fields')
                    ->label(__('admin.products.filters.has_missing_fields'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('missing_fields')->where('missing_fields', '!=', '[]'),
                        false: fn (Builder $query) => $query->where(fn (Builder $inner) => $inner->whereNull('missing_fields')->orWhere('missing_fields', '[]')),
                    ),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::updateAvailabilityAction(),
                    self::changeReviewStatusAction(),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function search(Builder $query, string $search): Builder
    {
        $term = SearchNormalizer::normalize($search);

        return $query->where(fn (Builder $inner) => $inner
            ->where('search_text_ar', 'like', "%{$term}%")
            ->orWhere('search_text_en', 'like', "%{$term}%")
            ->orWhere('slug', 'like', "%{$term}%")
            ->orWhere('appendix_no', $term));
    }

    private static function updateAvailabilityAction(): BulkAction
    {
        return BulkAction::make('updateAvailability')
            ->label(__('admin.products.actions.update_availability'))
            ->icon('heroicon-o-arrow-path')
            ->schema([
                Select::make('availability')
                    ->label(__('admin.products.fields.availability'))
                    ->options(Availability::options('ar'))
                    ->required(),
            ])
            ->action(function (Collection $records, array $data) {
                $records->each(fn (Product $product) => $product->update(['availability' => $data['availability']]));
            })
            ->deselectRecordsAfterCompletion();
    }

    private static function changeReviewStatusAction(): BulkAction
    {
        return BulkAction::make('changeReviewStatus')
            ->label(__('admin.products.actions.change_review_status'))
            ->icon('heroicon-o-check-badge')
            ->schema([
                Select::make('review_status')
                    ->label(__('admin.common.review_status'))
                    ->options(ReviewStatus::options('ar'))
                    ->required(),
            ])
            ->action(function (Collection $records, array $data) {
                $blocked = 0;

                foreach ($records as $product) {
                    try {
                        $product->update(['review_status' => $data['review_status']]);
                    } catch (PublicationBlocked) {
                        $blocked++;
                    }
                }

                if ($blocked > 0) {
                    Notification::make()
                        ->warning()
                        ->title(__('admin.products.actions.bulk_publish_partial', [
                            'published' => $records->count() - $blocked,
                            'blocked' => $blocked,
                        ]))
                        ->send();
                }
            })
            ->deselectRecordsAfterCompletion();
    }
}
