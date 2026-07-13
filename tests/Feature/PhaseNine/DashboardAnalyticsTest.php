<?php

use App\Enums\BillingCycle;
use App\Enums\InspectionStatus;
use App\Enums\InvoiceStatus;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use App\Enums\RentalApplicationStatus;
use App\Enums\Role;
use App\Enums\TenancyStatus;
use App\Enums\UnitOccupancyStatus;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\RentalApplication;
use App\Models\Tenancy;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Str;

function provisionDashboardUser(Role $role, string $name, string $email): User
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => $email,
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

test('admin dashboard shows live reporting metrics across modules', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = provisionDashboardUser(Role::Admin, 'Ada Admin', 'ada-admin@example.com');
    $landlord = provisionDashboardUser(Role::Landlord, 'Lara Landlord', 'lara-landlord@example.com');
    $caretaker = provisionDashboardUser(Role::Caretaker, 'Tunde Caretaker', 'tunde-caretaker@example.com');
    $tenant = provisionDashboardUser(Role::Tenant, 'Tina Tenant', 'tina-tenant@example.com');

    $property = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'caretaker_id' => $caretaker->caretakerProfile->id,
        'title' => 'Cedar Heights',
        'slug' => 'cedar-heights',
        'property_code' => 'PROP-CEDAR',
        'property_type' => PropertyType::ApartmentBuilding,
        'publish_status' => PropertyPublishStatus::Published,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'unit_name' => 'Unit A1',
        'unit_code' => 'UNIT-A1',
        'occupancy_status' => UnitOccupancyStatus::Occupied,
        'billing_cycle' => BillingCycle::Yearly,
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
        'message' => 'I would like to inspect this unit.',
    ]);

    RentalApplication::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'applicant_user_id' => $tenant->id,
        'status' => RentalApplicationStatus::Submitted,
        'source' => 'marketplace',
        'applicant_name' => $tenant->name,
        'applicant_email' => $tenant->email,
        'applicant_phone' => '+2348000000001',
        'employment_status' => 'Employed',
        'employer_name' => 'LeaseSmart Labs',
        'monthly_income' => 550000,
        'preferred_move_in_date' => today()->addWeeks(2)->toDateString(),
        'message' => 'Ready to move quickly.',
        'submitted_at' => now(),
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
        'tenant_phone' => '+2348000000001',
        'lease_start_date' => today()->subMonth()->toDateString(),
        'lease_end_date' => today()->addMonths(11)->toDateString(),
        'move_in_date' => today()->subWeeks(3)->toDateString(),
        'activated_at' => now()->subWeeks(3),
        'rent_amount' => 245000,
        'service_charge_amount' => 35000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    $invoice = Invoice::query()->create([
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'invoice_number' => 'INV-ADMIN-1001',
        'invoice_type' => 'rent',
        'issue_date' => today()->subDays(5)->toDateString(),
        'due_date' => today()->addDays(10)->toDateString(),
        'subtotal_amount' => 245000,
        'discount_amount' => 0,
        'total_amount' => 245000,
        'balance_amount' => 245000,
        'status' => InvoiceStatus::Issued,
        'issued_by' => $admin->id,
    ]);

    Payment::query()->create([
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'invoice_id' => $invoice->id,
        'submitted_by' => $tenant->id,
        'payment_reference' => 'PAY-ADMIN-1001',
        'payment_method' => PaymentMethod::BankTransfer,
        'amount' => 245000,
        'paid_at' => now()->subDay(),
        'status' => PaymentStatus::PendingVerification,
    ]);

    MaintenanceRequest::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'title' => 'Water heater fault',
        'description' => 'The heater trips after five minutes.',
        'category' => 'plumbing',
        'priority' => MaintenancePriority::High,
        'status' => MaintenanceStatus::Open,
        'reported_at' => now()->subHours(4),
        'assigned_to' => $caretaker->id,
        'created_by' => $tenant->id,
        'updated_by' => $tenant->id,
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response
        ->assertOk()
        ->assertSee('Platform Pulse')
        ->assertSee('Published Properties')
        ->assertSee('Open Work Items')
        ->assertSee('NGN 245,000')
        ->assertSee('Payment Reviews')
        ->assertSee('Recent Activity');
});

test('tenant dashboard is scoped to the signed in resident', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = provisionDashboardUser(Role::Admin, 'Ayo Admin', 'ayo-admin@example.com');
    $landlord = provisionDashboardUser(Role::Landlord, 'Bola Landlord', 'bola-landlord@example.com');
    $caretaker = provisionDashboardUser(Role::Caretaker, 'Chidi Caretaker', 'chidi-caretaker@example.com');
    $tenant = provisionDashboardUser(Role::Tenant, 'Tega Tenant', 'tega-tenant@example.com');
    $otherTenant = provisionDashboardUser(Role::Tenant, 'Other Tenant', 'other-tenant@example.com');

    $property = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'caretaker_id' => $caretaker->caretakerProfile->id,
        'title' => 'Maple Court Residences',
        'slug' => 'maple-court-residences',
        'property_code' => 'PROP-MAPLE',
        'property_type' => PropertyType::ApartmentBuilding,
        'publish_status' => PropertyPublishStatus::Published,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'unit_name' => 'Flat 2B',
        'unit_code' => 'UNIT-2B',
        'occupancy_status' => UnitOccupancyStatus::Occupied,
    ]);

    $otherProperty = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'caretaker_id' => $caretaker->caretakerProfile->id,
        'title' => 'Hidden Towers',
        'slug' => 'hidden-towers',
        'property_code' => 'PROP-HIDDEN',
        'property_type' => PropertyType::ApartmentBuilding,
        'publish_status' => PropertyPublishStatus::Published,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $otherUnit = PropertyUnit::factory()->create([
        'property_id' => $otherProperty->id,
        'unit_name' => 'Suite 9',
        'unit_code' => 'UNIT-9',
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
        'tenant_phone' => '+2348000000100',
        'lease_start_date' => today()->subMonth()->toDateString(),
        'lease_end_date' => today()->addMonths(11)->toDateString(),
        'move_in_date' => today()->subWeeks(2)->toDateString(),
        'activated_at' => now()->subWeeks(2),
        'rent_amount' => 180000,
        'service_charge_amount' => 25000,
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
        'tenant_phone' => '+2348000000101',
        'lease_start_date' => today()->subMonth()->toDateString(),
        'lease_end_date' => today()->addMonths(11)->toDateString(),
        'move_in_date' => today()->subWeeks(2)->toDateString(),
        'activated_at' => now()->subWeeks(2),
        'rent_amount' => 999999,
        'service_charge_amount' => 50000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    Invoice::query()->create([
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'invoice_number' => 'INV-TENANT-1001',
        'invoice_type' => 'rent',
        'issue_date' => today()->subDays(3)->toDateString(),
        'due_date' => today()->addDays(10)->toDateString(),
        'subtotal_amount' => 180000,
        'discount_amount' => 0,
        'total_amount' => 180000,
        'balance_amount' => 180000,
        'status' => InvoiceStatus::Issued,
        'issued_by' => $admin->id,
    ]);

    Invoice::query()->create([
        'tenancy_id' => $otherTenancy->id,
        'tenant_user_id' => $otherTenant->id,
        'invoice_number' => 'INV-TENANT-9999',
        'invoice_type' => 'rent',
        'issue_date' => today()->subDays(3)->toDateString(),
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
        'submitted_by' => $tenant->id,
        'payment_reference' => 'PAY-TENANT-1001',
        'payment_method' => PaymentMethod::BankTransfer,
        'amount' => 180000,
        'paid_at' => now()->subDay(),
        'status' => PaymentStatus::PendingVerification,
    ]);

    MaintenanceRequest::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'title' => 'Kitchen sink leak',
        'description' => 'Water is leaking beneath the sink cabinet.',
        'category' => 'plumbing',
        'priority' => MaintenancePriority::Medium,
        'status' => MaintenanceStatus::Assigned,
        'reported_at' => now()->subHours(6),
        'assigned_to' => $caretaker->id,
        'created_by' => $tenant->id,
        'updated_by' => $tenant->id,
    ]);

    MaintenanceRequest::query()->create([
        'property_id' => $otherProperty->id,
        'property_unit_id' => $otherUnit->id,
        'tenancy_id' => $otherTenancy->id,
        'tenant_user_id' => $otherTenant->id,
        'title' => 'Private issue',
        'description' => 'This should not appear on another tenant dashboard.',
        'category' => 'electrical',
        'priority' => MaintenancePriority::High,
        'status' => MaintenanceStatus::Open,
        'reported_at' => now()->subHours(2),
        'assigned_to' => $caretaker->id,
        'created_by' => $otherTenant->id,
        'updated_by' => $otherTenant->id,
    ]);

    $response = $this->actingAs($tenant)->get('/dashboard');

    $response
        ->assertOk()
        ->assertSee('Tenant Portal')
        ->assertSee('Current Property')
        ->assertSee('Maple Court Residences')
        ->assertSee('Flat 2B')
        ->assertSee('NGN 180,000')
        ->assertDontSee('Hidden Towers')
        ->assertDontSee('NGN 999,999');
});
