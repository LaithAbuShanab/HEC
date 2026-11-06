<?php

namespace App\Filament\Admin\Support;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Model;
use LogicException;

final class TranslatableActions
{
    public static function create(): CreateAction
    {
        return CreateAction::make()
            ->using(function (array $data, string $model): Model {
                $record = new $model;

                if (! $record instanceof Model) {
                    throw new LogicException("{$model} is not an Eloquent model.");
                }

                $record->fill(Translations::apply($record, $data))->save();

                return $record;
            });
    }

    public static function edit(): EditAction
    {
        return EditAction::make()
            ->mutateRecordDataUsing(fn (array $data, Model $record): array => Translations::fill($record, $data))
            ->using(function (Model $record, array $data): Model {
                $record->fill(Translations::apply($record, $data))->save();

                return $record;
            });
    }
}
