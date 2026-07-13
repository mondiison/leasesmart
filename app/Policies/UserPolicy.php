<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::Admin->value)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, User $managedUser): bool
    {
        return $user->is($managedUser);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $managedUser): bool
    {
        return $user->is($managedUser);
    }

    public function toggleActive(User $user, User $managedUser): bool
    {
        return false;
    }

    public function sendPasswordReset(User $user, User $managedUser): bool
    {
        return false;
    }
}
