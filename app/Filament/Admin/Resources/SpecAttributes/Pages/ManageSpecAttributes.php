<?php

namespace App\Filament\Admin\Resources\SpecAttributes\Pages;

use App\Filament\Admin\Resources\SpecAttributes\SpecAttributeResource;
use App\Filament\Admin\Support\TranslatableActions;
use Filament\Resources\Pages\ManageRecords;

class ManageSpecAttributes extends ManageRecords
{
    protected static string $resource = SpecAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            TranslatableActions::create(),
        ];
    }
}
