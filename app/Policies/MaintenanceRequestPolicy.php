<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\MaintenanceRequest;
use App\Models\User;

class MaintenanceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin->value)
            || $user->hasRole(Role::Landlord->value)
            || $user->hasRole(Role::Caretaker->value)
            || $user->hasRole(Role::Tenant->value);
    }

    public function view(User $user, MaintenanceRequest $request): bool
    {
        return $this->ownsOrManages($user, $request);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Admin->value)
            || $user->hasRole(Role::Landlord->value)
            || $user->hasRole(Role::Caretaker->value)
            || $user->hasRole(Role::Tenant->value);
    }

    public function update(User $user, MaintenanceRequest $request): bool
    {
        return $this->ownsOrManages($user, $request)
            && ! ($user->hasRole(Role::Tenant->value) && in_array($request->status->value, ['resolved', 'closed', 'cancelled'], true));
    }

    protected function ownsOrManages(User $user, MaintenanceRequest $request): bool
    {
        if ($user->hasRole(Role::Admin->value)) {
            return true;
        }

        if ($user->hasRole(Role::Landlord->value) && $user->landlordProfile?->is($request->property->landlord)) {
            return true;
        }

        if ($user->hasRole(Role::Caretaker->value) && ($user->caretakerProfile?->is($request->property->caretaker) || $request->assigned_to === $user->getKey())) {
            return true;
        }

        if ($user->hasRole(Role::Tenant->value) && $request->tenant_user_id === $user->getKey()) {
            return true;
        }

        return false;
    }
}
