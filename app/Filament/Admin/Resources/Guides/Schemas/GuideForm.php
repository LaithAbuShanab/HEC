<?php

namespace App\Filament\Admin\Resources\Guides\Schemas;

use App\Filament\Admin\Support\Fields;
use App\Filament\Admin\Support\SeoFields;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GuideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                Fields::title(),
                Fields::text('excerpt', rows: 2, max: 300),
                Fields::richText('body'),
            ]),
            Section::make()->columnSpanFull()->schema([
                Grid::make(2)->schema([Fields::slug(), Fields::reviewStatus()]),
                Grid::make(2)->schema([
                    TextInput::make('technical_reviewer')
                        ->label(__('admin.content.fields.technical_reviewer'))
                        ->helperText(__('admin.content.fields.technical_help'))
                        ->maxLength(120),
                    DatePicker::make('technically_reviewed_at')->label(__('admin.content.fields.technically_reviewed_at')),
                ]),
                Fields::image('cover'),
                Grid::make(2)->schema([
                    Fields::relatedProducts(),
                    Select::make('categories')
                        ->label(__('admin.content.fields.categories'))
                        ->relationship('categories', 'slug')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', app()->getLocale()))
                        ->multiple()
                        ->preload(),
                ]),
            ]),
            SeoFields::section()->columnSpanFull(),
        ]);
    }
}
