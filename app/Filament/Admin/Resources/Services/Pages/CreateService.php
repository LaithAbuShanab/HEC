<?php

namespace App\Filament\Admin\Resources\Services\Pages;

use App\Filament\Admin\Concerns\CreatesTranslations;
use App\Filament\Admin\Resources\Services\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    use CreatesTranslations;

    protected static string $resource = ServiceResource::class;
}
