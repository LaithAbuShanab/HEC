<?php

namespace App\Filament\Admin\Resources\Guides\Pages;

use App\Filament\Admin\Concerns\CreatesTranslations;
use App\Filament\Admin\Resources\Guides\GuideResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuide extends CreateRecord
{
    use CreatesTranslations;

    protected static string $resource = GuideResource::class;
}
