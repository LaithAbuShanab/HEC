<?php

namespace App\Filament\Admin\Resources\Redirects\Pages;

use App\Filament\Admin\Resources\Redirects\RedirectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRedirects extends ManageRecords
{
    protected static string $resource = RedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->mutateDataUsing(fn (array $data) => RedirectResource::clearTargetWhenGone($data)),
        ];
    }
}
