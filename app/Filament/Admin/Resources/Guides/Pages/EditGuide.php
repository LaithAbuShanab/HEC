<?php

namespace App\Filament\Admin\Resources\Guides\Pages;

use App\Filament\Admin\Concerns\EditsTranslations;
use App\Filament\Admin\Resources\Guides\GuideResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditGuide extends EditRecord
{
    use EditsTranslations;

    protected static string $resource = GuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
