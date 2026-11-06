<?php

namespace App\Filament\Admin\Resources\Faqs;

use App\Filament\Admin\Resources\Faqs\Pages\ManageFaqs;
use App\Filament\Admin\Resources\TranslatableResource;
use App\Filament\Admin\Support\Bilingual;
use App\Filament\Admin\Support\Columns;
use App\Filament\Admin\Support\Fields;
use App\Filament\Admin\Support\TranslatableActions;
use App\Models\Faq;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class FaqResource extends TranslatableResource
{
    protected static ?string $model = Faq::class;

    protected static string $langKey = 'faqs';

    protected static string $titleAttribute = 'question';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Bilingual::fields('question', __('admin.content.fields.question'), fn (string $path) => TextInput::make($path)->maxLength(255), required: true),
            Bilingual::fields('answer', __('admin.content.fields.answer'), fn (string $path) => Textarea::make($path)->rows(4)->maxLength(1500), required: true),
            Grid::make(4)->columnSpanFull()->schema([
                Select::make('group')->label(__('admin.content.fields.group'))->options(__('admin.content.faq_groups'))->default('general')->required(),
                Fields::reviewStatus(),
                Fields::sort(),
                Toggle::make('show_on_home')->label(__('admin.content.fields.show_on_home'))->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->defaultGroup('group')
            ->groups([
                Group::make('group')
                    ->label(__('admin.content.fields.group'))
                    ->getTitleFromRecordUsing(fn (Faq $record) => __("admin.content.faq_groups.{$record->group}")),
            ])
            ->columns([
                Columns::translated('question'),
                IconColumn::make('show_on_home')->label(__('admin.content.fields.show_on_home'))->boolean(),
                Columns::reviewStatus(),
                Columns::updatedAt()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('group')->label(__('admin.content.fields.group'))->options(__('admin.content.faq_groups')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                TranslatableActions::edit(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFaqs::route('/'),
        ];
    }
}
