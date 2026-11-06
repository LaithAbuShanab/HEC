<?php

namespace App\Filament\Admin\Concerns;

use App\Filament\Admin\Support\Translations;
use Illuminate\Database\Eloquent\Model;

trait CreatesTranslations
{
    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel());
        $record->fill(Translations::apply($record, $data))->save();

        return $record;
    }
}
