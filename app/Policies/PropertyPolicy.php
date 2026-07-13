<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('properties.manage') || $user->can('properties.view');
    }

    public function view(User $user, Property $property): bool
    {
        return $this->ownsOrManagesProperty($user, $property);
    }

    public function create(User $user): bool
    {
        return $user->can('properties.manage');
    }

    public function update(User $user, Property $property): bool
    {
        return $this->ownsOrManagesProperty($user, $property) && $user->can('properties.manage');
    }

    public function manageUnits(User $user, Property $property): bool
    {
        return $this->update($user, $property);
    }

    public function publish(User $user, Property $property): bool
    {
        return $this->update($user, $property);
    }

    protected function ownsOrManagesProperty(User $user, Property $property): bool
    {
        if ($user->hasRole(Role::Admin->value)) {
            return true;
        }

        if ($user->hasRole(Role::Landlord->value) && $user->landlordProfile?->is($property->landlord)) {
            return true;
        }

        if ($user->hasRole(Role::Caretaker->value) && $user->caretakerProfile?->is($property->caretaker)) {
            return true;
        }

        return false;
    }
}
