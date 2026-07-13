<?php

use App\Enums\BillingCycle;
use App\Enums\InvoiceStatus;
use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use App\Enums\Role;
use App\Enums\TenancyStatus;
use App\Enums\UnitOccupancyStatus;
use App\Models\Invoice;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenancy;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

function globalSearchUser(Role $role, string $name, string $email): User
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => $email,
        'email_verified_at' => now(),
    ]);

    $user->assignRole($role->value);

    match ($role) {
        Role::Landlord => $user->landlordProfile()->create(['company_name' => $name.' Holdings']),
        Role::Tenant => $user->tenantProfile()->create(['full_name' => $name, 'email' => $email]),
        default => null,
    };

    return $user;
}

test('admins can search across operational records', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = globalSearchUser(Role::Admin, 'Search Admin', 'search-admin@example.com');
    $landlord = globalSearchUser(Role::Landlord, 'Search Landlord', 'search-landlord@example.com');
    $tenant = globalSearchUser(Role::Tenant, 'Maya Tenant', 'maya-tenant@example.com');

    $property = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'title' => 'Global Search Towers',
        'slug' => 'global-search-towers',
        'property_code' => 'PROP-GLOBAL',
        'property_type' => PropertyType::ApartmentBuilding,
        'publish_status' => PropertyPublishStatus::Published,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'unit_name' => 'Search Suite 1',
        'occupancy_status' => UnitOccupancyStatus::Occupied,
    ]);

    $tenancy = Tenancy::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'tenant_user_id' => $tenant->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
        'status' => TenancyStatus::Active,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'lease_start_date' => now()->subMonth()->toDateString(),
        'rent_amount' => 250000,
        'service_charge_amount' => 25000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    Invoice::query()->create([
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'invoice_number' => 'INV-GLOBAL-001',
        'invoice_type' => 'rent',
        'issue_date' => today()->toDateString(),
        'due_date' => today()->addDays(7)->toDateString(),
        'subtotal_amount' => 250000,
        'discount_amount' => 0,
        'total_amount' => 250000,
        'balance_amount' => 250000,
        'status' => InvoiceStatus::Issued,
        'issued_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('search.index', ['q' => 'Global']))
        ->assertOk()
        ->assertSee('Global Search')
        ->assertSee('Global Search Towers')
        ->assertSee('INV-GLOBAL-001')
        ->assertSee('Maya Tenant');
});

test('tenant global search is scoped to their own records', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = globalSearchUser(Role::Admin, 'Scope Admin', 'scope-admin@example.com');
    $landlord = globalSearchUser(Role::Landlord, 'Scope Landlord', 'scope-landlord@example.com');
    $tenant = globalSearchUser(Role::Tenant, 'Visible Tenant', 'visible-tenant@example.com');
    $otherTenant = globalSearchUser(Role::Tenant, 'Hidden Tenant', 'hidden-tenant@example.com');

    $property = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'title' => 'Visible Search Court',
        'slug' => 'visible-search-court',
        'property_code' => 'PROP-VISIBLE',
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $otherProperty = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'title' => 'Hidden Search Court',
        'slug' => 'hidden-search-court',
        'property_code' => 'PROP-HIDDEN',
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create(['property_id' => $property->id, 'unit_name' => 'Visible Unit', 'occupancy_status' => UnitOccupancyStatus::Occupied]);
    $otherUnit = PropertyUnit::factory()->create(['property_id' => $otherProperty->id, 'unit_name' => 'Hidden Unit', 'occupancy_status' => UnitOccupancyStatus::Occupied]);

    Tenancy::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'tenant_user_id' => $tenant->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
        'status' => TenancyStatus::Active,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'lease_start_date' => now()->subMonth()->toDateString(),
        'rent_amount' => 250000,
        'service_charge_amount' => 25000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    Tenancy::query()->create([
        'property_id' => $otherProperty->id,
        'property_unit_id' => $otherUnit->id,
        'tenant_user_id' => $otherTenant->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
        'status' => TenancyStatus::Active,
        'tenant_name' => $otherTenant->name,
        'tenant_email' => $otherTenant->email,
        'lease_start_date' => now()->subMonth()->toDateString(),
        'rent_amount' => 999999,
        'service_charge_amount' => 25000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    $this->actingAs($tenant)
        ->get(route('search.index', ['q' => 'Search Court']))
        ->assertOk()
        ->assertSee('Visible Search Court')
        ->assertDontSee('Hidden Search Court')
        ->assertDontSee('Hidden Tenant');
});
