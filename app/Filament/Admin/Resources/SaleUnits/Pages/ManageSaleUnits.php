<?php

namespace App\Filament\Admin\Resources\SaleUnits\Pages;

use App\Filament\Admin\Resources\SaleUnits\SaleUnitResource;
use App\Filament\Admin\Support\TranslatableActions;
use Filament\Resources\Pages\ManageRecords;

class ManageSaleUnits extends ManageRecords
{
    protected static string $resource = SaleUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            TranslatableActions::create(),
        ];
    }
}
