<?php

namespace App\Filament\Admin\Resources\Services\Schemas;

use App\Filament\Admin\Support\Bilingual;
use App\Filament\Admin\Support\Fields;
use App\Filament\Admin\Support\SeoFields;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                Fields::title(),
                Fields::text('summary', rows: 2, max: 300),
                Fields::richText('body'),
                Fields::text('materials'),
                Bilingual::fields('capabilities', __('admin.content.fields.capabilities'), fn (string $path) => Textarea::make($path)
                    ->rows(3)
                    ->maxLength(800)
                    ->helperText(__('admin.content.fields.capabilities_help'))),
                Fields::text('pricing_requirements'),
            ]),
            Section::make()->columnSpanFull()->schema([
                Grid::make(3)->schema([Fields::slug(), Fields::reviewStatus(), Fields::sort()]),
                Fields::image('cover'),
                Fields::image('gallery', multiple: true),
                Fields::relatedProducts(),
            ]),
            SeoFields::section()->columnSpanFull(),
        ]);
    }
}
