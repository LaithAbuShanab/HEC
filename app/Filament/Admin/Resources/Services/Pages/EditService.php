<?php

namespace App\Filament\Admin\Resources\Services\Pages;

use App\Filament\Admin\Concerns\EditsTranslations;
use App\Filament\Admin\Resources\Services\ServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    use EditsTranslations;

    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
