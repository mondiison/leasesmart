<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Caretaker;
use App\Models\Landlord;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $admin = User::updateOrCreate(
            ['email' => 'admin@leasesmart.test'],
            [
                'name' => 'Ada Admin',
                'phone' => '+2348000000001',
                'password' => $password,
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );
        $admin->syncRoles([Role::Admin->value]);

        $landlord = User::updateOrCreate(
            ['email' => 'landlord@leasesmart.test'],
            [
                'name' => 'Lekan Landlord',
                'phone' => '+2348000000002',
                'password' => $password,
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );
        $landlord->syncRoles([Role::Landlord->value]);
        Landlord::updateOrCreate(
            ['user_id' => $landlord->id],
            [
                'company_name' => 'Lekan Estates',
                'address' => '12 Marina Crescent, Lagos',
                'notes' => 'Phase 0 demo landlord profile.',
            ],
        );

        $caretaker = User::updateOrCreate(
            ['email' => 'caretaker@leasesmart.test'],
            [
                'name' => 'Cynthia Caretaker',
                'phone' => '+2348000000003',
                'password' => $password,
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );
        $caretaker->syncRoles([Role::Caretaker->value]);
        Caretaker::updateOrCreate(
            ['user_id' => $caretaker->id],
            [
                'employee_code' => 'CT-001',
                'notes' => 'Phase 0 demo caretaker profile.',
            ],
        );

        $tenant = User::updateOrCreate(
            ['email' => 'tenant@leasesmart.test'],
            [
                'name' => 'Teni Tenant',
                'phone' => '+2348000000004',
                'password' => $password,
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );
        $tenant->syncRoles([Role::Tenant->value]);
        Tenant::updateOrCreate(
            ['user_id' => $tenant->id],
            [
                'full_name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'employment_status' => 'Employed',
                'employer_name' => 'LeaseSmart Demo',
                'monthly_income' => 350000,
                'notes' => 'Phase 0 demo tenant profile.',
            ],
        );
    }
}
