<?php

namespace App\Filament\Admin\Concerns;

use App\Domain\Catalog\Exceptions\PublicationBlocked;
use App\Filament\Admin\Support\Translations;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

trait EditsTranslations
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return Translations::fill($this->getRecord(), $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            $record->fill(Translations::apply($record, $data))->save();
        } catch (PublicationBlocked $exception) {
            Notification::make()
                ->danger()
                ->title(__('admin.publication.blocked'))
                ->body(collect($exception->blockers)->map(fn (string $key) => '• '.__("admin.publication.blockers.{$key}"))->implode("\n"))
                ->persistent()
                ->send();

            throw (new Halt)->rollBackDatabaseTransaction();
        }

        return $record;
    }
}
