<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Spatie\Permission\Models\Role as PermissionRole;

class RolePolicy
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

    public function view(User $user, PermissionRole $role): bool
    {
        return false;
    }
}
