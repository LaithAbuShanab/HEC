<?php

namespace App\Filament\Admin\Resources\WorkItems\Pages;

use App\Filament\Admin\Concerns\CreatesTranslations;
use App\Filament\Admin\Resources\WorkItems\WorkItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkItem extends CreateRecord
{
    use CreatesTranslations;

    protected static string $resource = WorkItemResource::class;
}
