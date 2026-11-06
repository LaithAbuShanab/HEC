<?php

namespace App\Filament\Admin\Resources\SearchSynonyms\Pages;

use App\Filament\Admin\Resources\SearchSynonyms\SearchSynonymResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSearchSynonyms extends ManageRecords
{
    protected static string $resource = SearchSynonymResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
