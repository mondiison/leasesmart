<?php

namespace App\Actions\Users;

use App\Enums\Role;
use App\Models\User;

class SyncUserProfileAction
{
    /**
     * @param array<string, mixed> $profileData
     */
    public function execute(User $user, Role $role, array $profileData = []): void
    {
        $user->landlordProfile()->delete();
        $user->caretakerProfile()->delete();
        $user->tenantProfile()->delete();

        match ($role) {
            Role::Admin => null,
            Role::Landlord => $user->landlordProfile()->create([
                'company_name' => $profileData['company_name'] ?? null,
                'address' => $profileData['address'] ?? null,
                'notes' => $profileData['notes'] ?? null,
            ]),
            Role::Caretaker => $user->caretakerProfile()->create([
                'employee_code' => $profileData['employee_code'] ?? null,
                'notes' => $profileData['notes'] ?? null,
            ]),
            Role::Tenant => $user->tenantProfile()->create([
                'full_name' => $profileData['full_name'] ?? $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'date_of_birth' => $profileData['date_of_birth'] ?? null,
                'gender' => $profileData['gender'] ?? null,
                'address' => $profileData['address'] ?? null,
                'emergency_contact_name' => $profileData['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $profileData['emergency_contact_phone'] ?? null,
                'employment_status' => $profileData['employment_status'] ?? null,
                'employer_name' => $profileData['employer_name'] ?? null,
                'monthly_income' => $profileData['monthly_income'] ?? null,
                'notes' => $profileData['notes'] ?? null,
            ]),
        };
    }
}
