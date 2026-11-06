<?php

namespace App\Filament\Admin\Resources\Products\RelationManagers;

use App\Domain\Localization\Locale;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.media.plural');
    }

    protected static function getModelLabel(): ?string
    {
        return __('admin.media.label');
    }

    public function form(Schema $schema): Schema
    {
        $fields = [];

        foreach (Locale::cases() as $locale) {
            $fields[] = TextInput::make("alt_{$locale->value}")
                ->label(__('admin.common.alt_text').' ('.__("admin.locales.{$locale->value}").')')
                ->maxLength(160)
                ->extraInputAttributes(['dir' => $locale->direction(), 'lang' => $locale->value]);
        }

        return $schema->components($fields);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('collection_name', ['main', 'gallery']))
            ->defaultSort('order_column')
            ->columns([
                ImageColumn::make('preview')
                    ->label('')
                    ->state(fn (Media $record) => $record->getUrl())
                    ->square()
                    ->imageSize(56),
                TextColumn::make('collection_name')->label(__('admin.media.collection'))->badge(),
                TextColumn::make('file_name')->label(__('admin.media.file'))->limit(30),
                TextColumn::make('alt_ar')
                    ->label(__('admin.common.alt_text').' ('.__('admin.locales.ar').')')
                    ->state(fn (Media $record) => $record->getCustomProperty('alt.ar'))
                    ->placeholder(__('admin.media.missing_alt')),
                TextColumn::make('alt_en')
                    ->label(__('admin.common.alt_text').' ('.__('admin.locales.en').')')
                    ->state(fn (Media $record) => $record->getCustomProperty('alt.en'))
                    ->placeholder(__('admin.media.missing_alt')),
            ])
            ->filters([
                TernaryFilter::make('missing_alt')
                    ->label(__('admin.media.missing_alt'))
                    ->queries(
                        true: fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                            ->whereNull('custom_properties->alt->ar')
                            ->orWhereNull('custom_properties->alt->en')),
                        false: fn (Builder $query) => $query
                            ->whereNotNull('custom_properties->alt->ar')
                            ->whereNotNull('custom_properties->alt->en'),
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(fn (Media $record) => [
                        'alt_ar' => $record->getCustomProperty('alt.ar'),
                        'alt_en' => $record->getCustomProperty('alt.en'),
                    ])
                    ->using(function (Media $record, array $data) {
                        foreach (Locale::cases() as $locale) {
                            $value = $data["alt_{$locale->value}"] ?? null;
                            filled($value)
                                ? $record->setCustomProperty("alt.{$locale->value}", $value)
                                : $record->forgetCustomProperty("alt.{$locale->value}");
                        }
                        $record->save();

                        return $record;
                    }),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
