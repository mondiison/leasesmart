<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\User;

class PropertyUnitPolicy
{
    public function viewAny(User $user, Property $property): bool
    {
        return $user->can('properties.manage') || $user->can('properties.view');
    }

    public function create(User $user, Property $property): bool
    {
        return $user->can('properties.manage') && $user->can('update', $property);
    }

    public function update(User $user, PropertyUnit $unit): bool
    {
        return $user->can('properties.manage') && $user->can('update', $unit->property);
    }
}
