<?php

use App\Enums\BillingCycle;
use App\Enums\InvoiceStatus;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use App\Enums\Role;
use App\Enums\TenancyStatus;
use App\Enums\UnitOccupancyStatus;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenancy;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

function phaseTenProvisionApiUser(Role $role, string $name, string $email, bool $active = true): User
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => $email,
        'password' => 'password',
        'is_active' => $active,
        'email_verified_at' => now(),
    ]);

    $user->assignRole($role->value);

    match ($role) {
        Role::Landlord => $user->landlordProfile()->create([
            'company_name' => $name.' Holdings',
        ]),
        Role::Caretaker => $user->caretakerProfile()->create([
            'employee_code' => 'CT-'.Str::upper(Str::random(6)),
        ]),
        Role::Tenant => $user->tenantProfile()->create([
            'full_name' => $name,
            'email' => $email,
            'phone' => '+2348000000000',
        ]),
        default => null,
    };

    return $user;
}

test('api tokens can be issued and the authenticated account endpoint returns the signed in user', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $tenant = phaseTenProvisionApiUser(Role::Tenant, 'Tapi Tenant', 'tapi-tenant@example.com');

    $tokenResponse = $this->postJson('/api/v1/tokens', [
        'email' => $tenant->email,
        'password' => 'password',
        'device_name' => 'iPhone 17 Pro',
    ]);

    $tokenResponse
        ->assertCreated()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.email', $tenant->email)
        ->assertJsonStructure(['plain_text_token']);

    Sanctum::actingAs($tenant);

    $this->getJson('/api/v1/account')
        ->assertOk()
        ->assertJsonPath('data.email', $tenant->email)
        ->assertJsonPath('data.role', Role::Tenant->value);
});

test('tenant mobile endpoints are scoped to the authenticated resident', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = phaseTenProvisionApiUser(Role::Admin, 'Api Admin', 'api-admin@example.com');
    $landlord = phaseTenProvisionApiUser(Role::Landlord, 'Api Landlord', 'api-landlord@example.com');
    $caretaker = phaseTenProvisionApiUser(Role::Caretaker, 'Api Caretaker', 'api-caretaker@example.com');
    $tenant = phaseTenProvisionApiUser(Role::Tenant, 'Scoped Tenant', 'scoped-tenant@example.com');
    $otherTenant = phaseTenProvisionApiUser(Role::Tenant, 'Other Tenant', 'other-api-tenant@example.com');

    $property = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'caretaker_id' => $caretaker->caretakerProfile->id,
        'title' => 'Azure Gardens',
        'slug' => 'azure-gardens',
        'property_code' => 'PROP-AZURE',
        'property_type' => PropertyType::ApartmentBuilding,
        'publish_status' => PropertyPublishStatus::Published,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'unit_name' => 'Suite C3',
        'unit_code' => 'UNIT-C3',
        'occupancy_status' => UnitOccupancyStatus::Occupied,
    ]);

    $otherProperty = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'caretaker_id' => $caretaker->caretakerProfile->id,
        'title' => 'Private Court',
        'slug' => 'private-court',
        'property_code' => 'PROP-PRIVATE',
        'property_type' => PropertyType::ApartmentBuilding,
        'publish_status' => PropertyPublishStatus::Published,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $otherUnit = PropertyUnit::factory()->create([
        'property_id' => $otherProperty->id,
        'unit_name' => 'Unit Z9',
        'unit_code' => 'UNIT-Z9',
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
        'tenant_phone' => '+2348000000200',
        'lease_start_date' => today()->subMonth()->toDateString(),
        'lease_end_date' => today()->addMonths(11)->toDateString(),
        'move_in_date' => today()->subWeeks(2)->toDateString(),
        'activated_at' => now()->subWeeks(2),
        'rent_amount' => 220000,
        'service_charge_amount' => 30000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    $otherTenancy = Tenancy::query()->create([
        'property_id' => $otherProperty->id,
        'property_unit_id' => $otherUnit->id,
        'tenant_user_id' => $otherTenant->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
        'status' => TenancyStatus::Active,
        'tenant_name' => $otherTenant->name,
        'tenant_email' => $otherTenant->email,
        'tenant_phone' => '+2348000000201',
        'lease_start_date' => today()->subMonth()->toDateString(),
        'lease_end_date' => today()->addMonths(11)->toDateString(),
        'move_in_date' => today()->subWeeks(2)->toDateString(),
        'activated_at' => now()->subWeeks(2),
        'rent_amount' => 999999,
        'service_charge_amount' => 45000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    $invoice = Invoice::query()->create([
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'invoice_number' => 'INV-API-1001',
        'invoice_type' => 'rent',
        'issue_date' => today()->subDays(4)->toDateString(),
        'due_date' => today()->addDays(10)->toDateString(),
        'subtotal_amount' => 220000,
        'discount_amount' => 0,
        'total_amount' => 220000,
        'balance_amount' => 220000,
        'status' => InvoiceStatus::Issued,
        'issued_by' => $admin->id,
    ]);

    Invoice::query()->create([
        'tenancy_id' => $otherTenancy->id,
        'tenant_user_id' => $otherTenant->id,
        'invoice_number' => 'INV-API-9999',
        'invoice_type' => 'rent',
        'issue_date' => today()->subDays(4)->toDateString(),
        'due_date' => today()->addDays(10)->toDateString(),
        'subtotal_amount' => 999999,
        'discount_amount' => 0,
        'total_amount' => 999999,
        'balance_amount' => 999999,
        'status' => InvoiceStatus::Issued,
        'issued_by' => $admin->id,
    ]);

    Payment::query()->create([
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'invoice_id' => $invoice->id,
        'submitted_by' => $tenant->id,
        'payment_reference' => 'PAY-API-1001',
        'payment_method' => PaymentMethod::BankTransfer,
        'amount' => 220000,
        'paid_at' => now()->subDay(),
        'status' => PaymentStatus::PendingVerification,
    ]);

    Payment::query()->create([
        'tenancy_id' => $otherTenancy->id,
        'tenant_user_id' => $otherTenant->id,
        'submitted_by' => $otherTenant->id,
        'payment_reference' => 'PAY-API-9999',
        'payment_method' => PaymentMethod::BankTransfer,
        'amount' => 999999,
        'paid_at' => now()->subDay(),
        'status' => PaymentStatus::PendingVerification,
    ]);

    MaintenanceRequest::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'title' => 'Air conditioning service',
        'description' => 'Cooling is weak in the living room.',
        'category' => 'electrical',
        'priority' => MaintenancePriority::Medium,
        'status' => MaintenanceStatus::Assigned,
        'reported_at' => now()->subHours(8),
        'assigned_to' => $caretaker->id,
        'created_by' => $tenant->id,
        'updated_by' => $tenant->id,
    ]);

    MaintenanceRequest::query()->create([
        'property_id' => $otherProperty->id,
        'property_unit_id' => $otherUnit->id,
        'tenancy_id' => $otherTenancy->id,
        'tenant_user_id' => $otherTenant->id,
        'title' => 'Hidden request',
        'description' => 'Should not leak into the wrong mobile account.',
        'category' => 'plumbing',
        'priority' => MaintenancePriority::High,
        'status' => MaintenanceStatus::Open,
        'reported_at' => now()->subHours(4),
        'assigned_to' => $caretaker->id,
        'created_by' => $otherTenant->id,
        'updated_by' => $otherTenant->id,
    ]);

    Sanctum::actingAs($tenant);

    $this->getJson('/api/v1/tenancies')
        ->assertOk()
        ->assertJsonFragment(['tenant_name' => $tenant->name])
        ->assertJsonMissing(['tenant_name' => $otherTenant->name]);

    $this->getJson('/api/v1/invoices')
        ->assertOk()
        ->assertJsonFragment(['invoice_number' => 'INV-API-1001'])
        ->assertJsonMissing(['invoice_number' => 'INV-API-9999']);

    $this->getJson('/api/v1/payments')
        ->assertOk()
        ->assertJsonFragment(['payment_reference' => 'PAY-API-1001'])
        ->assertJsonMissing(['payment_reference' => 'PAY-API-9999']);

    $this->getJson('/api/v1/maintenance-requests')
        ->assertOk()
        ->assertJsonFragment(['title' => 'Air conditioning service'])
        ->assertJsonMissing(['title' => 'Hidden request']);
});

test('inactive users receive json responses from the api layer', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $inactiveTenant = phaseTenProvisionApiUser(Role::Tenant, 'Inactive Tenant', 'inactive-tenant@example.com', active: false);

    $this->postJson('/api/v1/tokens', [
        'email' => $inactiveTenant->email,
        'password' => 'password',
        'device_name' => 'Android Client',
    ])->assertForbidden();

    Sanctum::actingAs($inactiveTenant);

    $this->getJson('/api/v1/account')
        ->assertForbidden()
        ->assertJsonPath('message', 'Your account is currently inactive. Please contact support.');
});
