<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Inspection;
use App\Models\User;

class InspectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin->value)
            || $user->hasRole(Role::Landlord->value)
            || $user->hasRole(Role::Caretaker->value);
    }

    public function view(User $user, Inspection $inspection): bool
    {
        return $this->ownsOrManages($user, $inspection);
    }

    public function update(User $user, Inspection $inspection): bool
    {
        return $this->ownsOrManages($user, $inspection);
    }

    protected function ownsOrManages(User $user, Inspection $inspection): bool
    {
        if ($user->hasRole(Role::Admin->value)) {
            return true;
        }

        if ($user->hasRole(Role::Landlord->value) && $user->landlordProfile?->is($inspection->property->landlord)) {
            return true;
        }

        if ($user->hasRole(Role::Caretaker->value) && $user->caretakerProfile?->is($inspection->property->caretaker)) {
            return true;
        }

        return false;
    }
}
