<?php

namespace App\Filament\Admin\Support;

use App\Domain\Catalog\Enums\ReviewStatus;
use App\Models\Contracts\Translatable;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

final class Columns
{
    public static function translated(string $name, ?string $label = null): TextColumn
    {
        return TextColumn::make($name)
            ->label($label ?? __("admin.content.fields.{$name}"))
            ->state(fn (Model $record) => $record instanceof Translatable ? $record->getTranslation($name, 'ar') : null)
            ->description(fn (Model $record) => $record instanceof Translatable ? $record->getTranslation($name, 'en', false) : null)
            ->wrap();
    }

    public static function reviewStatus(): TextColumn
    {
        return TextColumn::make('review_status')
            ->label(__('admin.common.review_status'))
            ->badge()
            ->formatStateUsing(fn (ReviewStatus $state) => $state->label('ar'))
            ->color(fn (ReviewStatus $state) => match ($state) {
                ReviewStatus::Draft => 'gray',
                ReviewStatus::Pending => 'warning',
                ReviewStatus::Published => 'success',
            });
    }

    public static function updatedAt(): TextColumn
    {
        return TextColumn::make('updated_at')->label(__('admin.common.updated_at'))->since()->sortable();
    }
}
