<?php

namespace App\Policies;

use App\Domain\Access\Role;
use App\Models\User;

class QuoteRequestPolicy
{
    private function isSales(User $user): bool
    {
        return $user->hasRole(Role::Sales->value);
    }

    public function viewAny(User $user): bool
    {
        return $this->isSales($user);
    }

    public function view(User $user): bool
    {
        return $this->isSales($user);
    }

    public function update(User $user): bool
    {
        return $this->isSales($user);
    }

    public function export(User $user): bool
    {
        return $this->isSales($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user): bool
    {
        return false;
    }
}
