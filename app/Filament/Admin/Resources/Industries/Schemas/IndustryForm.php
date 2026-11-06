<?php

namespace App\Filament\Admin\Resources\Industries\Schemas;

use App\Filament\Admin\Support\Fields;
use App\Filament\Admin\Support\SeoFields;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IndustryForm
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
                Grid::make(3)->schema([Fields::slug(), Fields::reviewStatus(), Fields::sort()]),
                Fields::image('cover'),
                Fields::relatedProducts(),
            ]),
            SeoFields::section()->columnSpanFull(),
        ]);
    }
}
