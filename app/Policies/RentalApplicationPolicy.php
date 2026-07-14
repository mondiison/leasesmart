<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\RentalApplication;
use App\Models\User;

class RentalApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin->value)
            || $user->hasRole(Role::Landlord->value)
            || $user->hasRole(Role::Tenant->value);
    }

    public function view(User $user, RentalApplication $application): bool
    {
        return $this->ownsOrManages($user, $application);
    }

    public function update(User $user, RentalApplication $application): bool
    {
        return $user->hasRole(Role::Admin->value)
            || ($user->hasRole(Role::Landlord->value) && $user->landlordProfile?->is($application->property->landlord));
    }

    protected function ownsOrManages(User $user, RentalApplication $application): bool
    {
        if ($user->hasRole(Role::Admin->value)) {
            return true;
        }

        if ($user->hasRole(Role::Landlord->value) && $user->landlordProfile?->is($application->property->landlord)) {
            return true;
        }

        if ($user->hasRole(Role::Tenant->value)) {
            return $application->applicant_user_id === $user->getKey()
                || $application->applicant_email === $user->email;
        }

        return false;
    }
}
