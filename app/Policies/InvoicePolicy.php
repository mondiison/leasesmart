<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin->value)
            || $user->hasRole(Role::Landlord->value)
            || $user->hasRole(Role::Tenant->value);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->ownsOrManages($user, $invoice);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Admin->value) || $user->hasRole(Role::Landlord->value);
    }

    protected function ownsOrManages(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole(Role::Admin->value)) {
            return true;
        }

        if ($user->hasRole(Role::Landlord->value) && $user->landlordProfile?->is($invoice->tenancy->property->landlord)) {
            return true;
        }

        if ($user->hasRole(Role::Tenant->value) && $invoice->tenant_user_id === $user->getKey()) {
            return true;
        }

        return false;
    }
}
