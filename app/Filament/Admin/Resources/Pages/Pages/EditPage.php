<?php

namespace App\Filament\Admin\Resources\Pages\Pages;

use App\Filament\Admin\Concerns\EditsTranslations;
use App\Filament\Admin\Resources\Pages\PageResource;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    use EditsTranslations;

    protected static string $resource = PageResource::class;
}
