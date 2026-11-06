<?php

namespace App\Filament\Admin\Resources\Industries\Pages;

use App\Filament\Admin\Concerns\CreatesTranslations;
use App\Filament\Admin\Resources\Industries\IndustryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIndustry extends CreateRecord
{
    use CreatesTranslations;

    protected static string $resource = IndustryResource::class;
}
