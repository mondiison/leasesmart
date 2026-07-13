<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Tenancy;
use App\Models\User;

class TenancyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin->value)
            || $user->hasRole(Role::Landlord->value)
            || $user->hasRole(Role::Caretaker->value)
            || $user->hasRole(Role::Tenant->value);
    }

    public function view(User $user, Tenancy $tenancy): bool
    {
        return $this->ownsOrManages($user, $tenancy);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Admin->value) || $user->hasRole(Role::Landlord->value);
    }

    public function update(User $user, Tenancy $tenancy): bool
    {
        return ($user->hasRole(Role::Admin->value) || $user->hasRole(Role::Landlord->value))
            && $this->ownsOrManages($user, $tenancy);
    }

    protected function ownsOrManages(User $user, Tenancy $tenancy): bool
    {
        if ($user->hasRole(Role::Admin->value)) {
            return true;
        }

        if ($user->hasRole(Role::Landlord->value) && $user->landlordProfile?->is($tenancy->property->landlord)) {
            return true;
        }

        if ($user->hasRole(Role::Caretaker->value) && $user->caretakerProfile?->is($tenancy->property->caretaker)) {
            return true;
        }

        if ($user->hasRole(Role::Tenant->value) && $tenancy->tenant_user_id === $user->getKey()) {
            return true;
        }

        return false;
    }
}
