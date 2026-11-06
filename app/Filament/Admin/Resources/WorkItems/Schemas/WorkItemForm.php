<?php

namespace App\Filament\Admin\Resources\WorkItems\Schemas;

use App\Filament\Admin\Support\Fields;
use App\Filament\Admin\Support\SeoFields;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                Fields::title(),
                Fields::text('summary', rows: 2, max: 300),
                Fields::richText('body'),
            ]),
            Section::make()->columnSpanFull()->schema([
                Grid::make(4)->schema([
                    Fields::slug(),
                    Fields::reviewStatus(),
                    DatePicker::make('performed_on')->label(__('admin.content.fields.performed_on'))->maxDate(now()),
                    Fields::sort(),
                ]),
                Fields::image('gallery', multiple: true)->helperText(__('admin.content.fields.gallery_help')),
                Fields::relatedProducts(),
            ]),
            SeoFields::section()->columnSpanFull(),
        ]);
    }
}
