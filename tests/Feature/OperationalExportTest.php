<?php

use App\Enums\BillingCycle;
use App\Enums\InvoiceStatus;
use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use App\Enums\Role;
use App\Enums\TenancyStatus;
use App\Enums\UnitOccupancyStatus;
use App\Models\Invoice;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\Tenancy;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

function exportUser(Role $role, string $name, string $email): User
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

function exportTenancyFixture(User $admin, User $landlord, User $tenant, string $propertyTitle, string $invoiceNumber, int $amount = 250000): Tenancy
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
        'rent_amount' => $amount,
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
        'subtotal_amount' => $amount,
        'discount_amount' => 0,
        'total_amount' => $amount,
        'balance_amount' => $amount,
        'status' => InvoiceStatus::Issued,
        'issued_by' => $admin->id,
    ]);

    return $tenancy;
}

test('admins can export filtered billing invoices as csv', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = exportUser(Role::Admin, 'Export Admin', 'export-admin@example.com');
    $landlord = exportUser(Role::Landlord, 'Export Landlord', 'export-landlord@example.com');
    $tenant = exportUser(Role::Tenant, 'Export Tenant', 'export-tenant@example.com');

    exportTenancyFixture($admin, $landlord, $tenant, 'Export Heights', 'INV-EXPORT-001');

    $response = $this->actingAs($admin)
        ->get(route('exports.show', ['type' => 'billing-invoices', 'q' => 'EXPORT']));

    $response
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())
        ->toContain('Invoice Number')
        ->toContain('INV-EXPORT-001')
        ->toContain('Export Heights');
});

test('tenant exports are scoped to their own records', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = exportUser(Role::Admin, 'Scope Export Admin', 'scope-export-admin@example.com');
    $landlord = exportUser(Role::Landlord, 'Scope Export Landlord', 'scope-export-landlord@example.com');
    $tenant = exportUser(Role::Tenant, 'Visible Export Tenant', 'visible-export@example.com');
    $otherTenant = exportUser(Role::Tenant, 'Hidden Export Tenant', 'hidden-export@example.com');

    exportTenancyFixture($admin, $landlord, $tenant, 'Visible Export Court', 'INV-VISIBLE-001');
    exportTenancyFixture($admin, $landlord, $otherTenant, 'Hidden Export Court', 'INV-HIDDEN-001', 999999);

    $response = $this->actingAs($tenant)
        ->get(route('exports.show', ['type' => 'tenancies', 'q' => 'Export Court']));

    $response->assertOk();

    expect($response->streamedContent())
        ->toContain('Visible Export Court')
        ->toContain('Visible Export Tenant')
        ->not->toContain('Hidden Export Court')
        ->not->toContain('Hidden Export Tenant');
});
