<?php

namespace App\Filament\Admin\Resources\SearchSynonyms;

use App\Filament\Admin\Resources\SearchSynonyms\Pages\ManageSearchSynonyms;
use App\Models\SearchSynonym;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TagsInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SearchSynonymResource extends Resource
{
    protected static ?string $model = SearchSynonym::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('admin.navigation.catalog');
    }

    public static function getModelLabel(): string
    {
        return __('admin.synonyms.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.synonyms.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TagsInput::make('terms')
                ->label(__('admin.synonyms.fields.terms'))
                ->helperText(__('admin.synonyms.fields.terms_help'))
                ->required()
                ->rules(['array', 'min:2'])
                ->nestedRecursiveRules(['string', 'max:60'])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('terms')
                    ->label(__('admin.synonyms.fields.terms'))
                    ->badge()
                    ->separator(',')
                    ->searchable(query: fn (Builder $query, string $search) => $query->where('terms', 'like', '%'.trim(json_encode($search, JSON_UNESCAPED_UNICODE) ?: '', '"').'%')),
                TextColumn::make('updated_at')->label(__('admin.common.updated_at'))->since(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSearchSynonyms::route('/'),
        ];
    }
}
