<?php

namespace App\Filament\Admin\Resources\QuoteRequests\Pages;

use App\Filament\Admin\Resources\QuoteRequests\QuoteRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListQuoteRequests extends ListRecords
{
    protected static string $resource = QuoteRequestResource::class;
}
