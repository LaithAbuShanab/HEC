<?php

namespace App\Filament\Admin\Resources\WorkItems\Pages;

use App\Filament\Admin\Resources\WorkItems\WorkItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkItems extends ListRecords
{
    protected static string $resource = WorkItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
