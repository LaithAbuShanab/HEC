<?php

namespace App\Policies;

use App\Domain\Access\Role;
use App\Models\User;

class EditorialPolicy
{
    private function canEdit(User $user): bool
    {
        return $user->hasRole(Role::Editor->value);
    }

    public function viewAny(User $user): bool
    {
        return $this->canEdit($user);
    }

    public function view(User $user): bool
    {
        return $this->canEdit($user);
    }

    public function create(User $user): bool
    {
        return $this->canEdit($user);
    }

    public function update(User $user): bool
    {
        return $this->canEdit($user);
    }

    public function delete(User $user): bool
    {
        return $this->canEdit($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canEdit($user);
    }

    public function restore(User $user): bool
    {
        return $this->canEdit($user);
    }

    public function restoreAny(User $user): bool
    {
        return $this->canEdit($user);
    }

    public function reorder(User $user): bool
    {
        return $this->canEdit($user);
    }

    public function forceDelete(User $user): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
