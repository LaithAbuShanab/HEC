<?php

namespace App\Filament\Admin\Resources\Pages;

use App\Filament\Admin\Resources\Pages\Pages\EditPage;
use App\Filament\Admin\Resources\Pages\Pages\ListPages;
use App\Filament\Admin\Resources\TranslatableResource;
use App\Filament\Admin\Support\Columns;
use App\Filament\Admin\Support\Fields;
use App\Filament\Admin\Support\SeoFields;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PageResource extends TranslatableResource
{
    protected static ?string $model = Page::class;

    protected static string $langKey = 'pages';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                Grid::make(2)->schema([
                    Select::make('key')
                        ->label(__('admin.content.fields.key'))
                        ->options(__('admin.content.page_keys'))
                        ->disabled()
                        ->dehydrated(false),
                    Fields::reviewStatus(),
                ]),
                Fields::title(),
                Fields::richText('body'),
            ]),
            SeoFields::section()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('admin.content.fields.key'))
                    ->formatStateUsing(fn (string $state) => __("admin.content.page_keys.{$state}")),
                Columns::translated('title'),
                Columns::reviewStatus(),
                Columns::updatedAt(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->paginated(false);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
