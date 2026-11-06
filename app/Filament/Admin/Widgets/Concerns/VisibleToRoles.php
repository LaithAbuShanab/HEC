<?php

namespace App\Filament\Admin\Widgets\Concerns;

use App\Domain\Access\Role;
use App\Models\User;

trait VisibleToRoles
{
    abstract protected static function roles(): array;

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User || ! $user->is_active) {
            return false;
        }

        return $user->isAdmin() || $user->hasAnyRole(array_map(fn (Role $role) => $role->value, static::roles()));
    }
}
