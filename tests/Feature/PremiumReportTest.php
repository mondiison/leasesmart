<?php

use App\Enums\BillingCycle;
use App\Enums\InspectionStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use App\Enums\Role;
use App\Enums\TenancyStatus;
use App\Enums\UnitOccupancyStatus;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

function premiumReportUser(Role $role, string $name, string $email): User
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => $email,
        'email_verified_at' => now(),
    ]);

    $user->assignRole($role->value);

    match ($role) {
        Role::Landlord => Landlord::query()->create(['user_id' => $user->id, 'company_name' => $name.' Holdings']),
        Role::Tenant => Tenant::query()->create(['user_id' => $user->id, 'full_name' => $name, 'email' => $email]),
        default => null,
    };

    return $user;
}

function premiumReportTenancy(User $admin, User $landlord, User $tenant, string $propertyTitle, string $invoiceNumber): Tenancy
{
    $property = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'title' => $propertyTitle,
        'property_type' => PropertyType::ApartmentBuilding,
        'publish_status' => PropertyPublishStatus::Published,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'unit_name' => $propertyTitle.' Unit',
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
        'invoice_number' => $invoiceNumber,
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

    return $tenancy;
}

test('admins can open premium chart report for invoices', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = premiumReportUser(Role::Admin, 'Premium Admin', 'premium-admin@example.com');
    $landlord = premiumReportUser(Role::Landlord, 'Premium Landlord', 'premium-landlord@example.com');
    $tenant = premiumReportUser(Role::Tenant, 'Premium Tenant', 'premium-tenant@example.com');

    premiumReportTenancy($admin, $landlord, $tenant, 'Premium Heights', 'INV-PREMIUM-001');

    $this->actingAs($admin)
        ->get(route('reports.premium', ['type' => 'billing-invoices', 'q' => 'PREMIUM']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('tenant premium report is scoped to their own tenancies', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = premiumReportUser(Role::Admin, 'Scoped Premium Admin', 'scoped-premium-admin@example.com');
    $landlord = premiumReportUser(Role::Landlord, 'Scoped Premium Landlord', 'scoped-premium-landlord@example.com');
    $tenant = premiumReportUser(Role::Tenant, 'Visible Premium Tenant', 'visible-premium@example.com');
    $otherTenant = premiumReportUser(Role::Tenant, 'Hidden Premium Tenant', 'hidden-premium@example.com');

    premiumReportTenancy($admin, $landlord, $tenant, 'Visible Premium Court', 'INV-VISIBLE-PREMIUM');
    premiumReportTenancy($admin, $landlord, $otherTenant, 'Hidden Premium Court', 'INV-HIDDEN-PREMIUM');

    $this->actingAs($tenant)
        ->get(route('reports.premium', ['type' => 'tenancies', 'q' => 'Premium Court']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('admins can open premium pdf report for inspections', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = premiumReportUser(Role::Admin, 'Inspection Premium Admin', 'inspection-premium-admin@example.com');
    $landlord = premiumReportUser(Role::Landlord, 'Inspection Premium Landlord', 'inspection-premium-landlord@example.com');
    $tenant = premiumReportUser(Role::Tenant, 'Inspection Premium Tenant', 'inspection-premium-tenant@example.com');

    $property = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'title' => 'Inspection Premium Towers',
        'property_type' => PropertyType::ApartmentBuilding,
        'publish_status' => PropertyPublishStatus::Published,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'unit_name' => 'Inspection Suite 1',
        'occupancy_status' => UnitOccupancyStatus::Vacant,
    ]);

    Inspection::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'requester_user_id' => $tenant->id,
        'status' => InspectionStatus::Requested,
        'source' => 'marketplace',
        'requester_name' => $tenant->name,
        'requester_email' => $tenant->email,
        'requester_phone' => '+2348000000001',
        'requested_for_date' => today()->addDay()->toDateString(),
        'requested_for_time' => '10:00',
    ]);

    $this->actingAs($admin)
        ->get(route('reports.premium', ['type' => 'inspections', 'q' => 'Inspection Premium']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
