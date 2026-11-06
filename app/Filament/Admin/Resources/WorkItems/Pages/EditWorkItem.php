<?php

namespace App\Filament\Admin\Resources\WorkItems\Pages;

use App\Filament\Admin\Concerns\EditsTranslations;
use App\Filament\Admin\Resources\WorkItems\WorkItemResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkItem extends EditRecord
{
    use EditsTranslations;

    protected static string $resource = WorkItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
