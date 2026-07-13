<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin->value)
            || $user->hasRole(Role::Landlord->value)
            || $user->hasRole(Role::Tenant->value);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->ownsOrManages($user, $payment);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Admin->value)
            || $user->hasRole(Role::Landlord->value)
            || $user->hasRole(Role::Tenant->value);
    }

    public function review(User $user, Payment $payment): bool
    {
        return ($user->hasRole(Role::Admin->value) || $user->hasRole(Role::Landlord->value))
            && $this->ownsOrManages($user, $payment);
    }

    protected function ownsOrManages(User $user, Payment $payment): bool
    {
        if ($user->hasRole(Role::Admin->value)) {
            return true;
        }

        if ($user->hasRole(Role::Landlord->value) && $user->landlordProfile?->is($payment->tenancy?->property->landlord)) {
            return true;
        }

        if ($user->hasRole(Role::Tenant->value) && $payment->tenant_user_id === $user->getKey()) {
            return true;
        }

        return false;
    }
}
