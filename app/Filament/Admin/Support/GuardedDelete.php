<?php

namespace App\Filament\Admin\Support;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

final class GuardedDelete
{
    public static function make(): DeleteAction
    {
        return DeleteAction::make()
            ->before(function (DeleteAction $action, Model $record) {
                $count = method_exists($record, 'usageCount') ? (int) $record->usageCount() : 0;

                if ($count === 0) {
                    return;
                }

                Notification::make()->danger()->title(__('admin.in_use', ['count' => $count]))->send();
                $action->cancel();
            });
    }
}
