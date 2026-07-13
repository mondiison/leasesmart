<?php

use App\Enums\BillingCycle;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Enums\PropertyPublishStatus;
use App\Enums\RentalApplicationStatus;
use App\Enums\Role;
use App\Enums\TenancyStatus;
use App\Enums\UnitOccupancyStatus;
use App\Livewire\Tenancies\Index as TenanciesIndex;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\RentalApplication;
use App\Models\Tenant;
use App\Models\Tenancy;
use App\Models\User;
use App\Notifications\TenancyDocumentAddedNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

$makeAdmin = function (): User {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Admin->value);

    return $user;
};

$makeLandlord = function (string $company = 'Acme Estates'): User {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Landlord->value);
    Landlord::query()->create(['user_id' => $user->id, 'company_name' => $company]);

    return $user;
};

$makeTenant = function (): User {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Tenant->value);
    Tenant::query()->create(['user_id' => $user->id, 'full_name' => $user->name, 'email' => $user->email]);

    return $user;
};

test('admins can convert approved applications into tenancies', function () use ($makeAdmin, $makeLandlord, $makeTenant) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $tenantUser = $makeTenant();

    $property = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'occupancy_status' => UnitOccupancyStatus::Vacant,
        'billing_cycle' => BillingCycle::Yearly,
        'is_listed' => true,
    ]);

    $application = RentalApplication::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'applicant_user_id' => $tenantUser->id,
        'status' => RentalApplicationStatus::Approved,
        'source' => 'marketplace',
        'applicant_name' => $tenantUser->name,
        'applicant_email' => $tenantUser->email,
        'applicant_phone' => '08030000000',
        'submitted_at' => now()->subDays(2),
        'decided_at' => now()->subDay(),
    ]);

    Livewire::actingAs($admin)
        ->test(TenanciesIndex::class)
        ->set("createStatus.{$application->id}", TenancyStatus::Active->value)
        ->set("createLeaseStartDates.{$application->id}", now()->toDateString())
        ->set("createLeaseEndDates.{$application->id}", now()->addYear()->toDateString())
        ->set("createMoveInDates.{$application->id}", now()->toDateString())
        ->set("createRentAmounts.{$application->id}", '2500000')
        ->set("createServiceCharges.{$application->id}", '300000')
        ->set("createBillingCycles.{$application->id}", BillingCycle::Yearly->value)
        ->call('createFromApplication', $application->id)
        ->assertHasNoErrors();

    $tenancy = Tenancy::query()->firstOrFail();

    expect($tenancy->status)->toBe(TenancyStatus::Active);
    expect($tenancy->tenant_user_id)->toBe($tenantUser->id);
    expect($application->fresh()->status)->toBe(RentalApplicationStatus::Converted);
    expect($unit->fresh()->occupancy_status)->toBe(UnitOccupancyStatus::Occupied);

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'tenancy_created',
        'loggable_id' => $tenancy->id,
    ]);
});

test('landlords can manage tenancies for their properties', function () use ($makeAdmin, $makeLandlord, $makeTenant) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $tenantUser = $makeTenant();

    $property = Property::factory()->create([
        'title' => 'Palm Heights',
        'landlord_id' => $landlord->landlordProfile->id,
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'occupancy_status' => UnitOccupancyStatus::Reserved,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    $tenancy = Tenancy::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'tenant_user_id' => $tenantUser->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
        'status' => TenancyStatus::PendingActivation,
        'tenant_name' => $tenantUser->name,
        'tenant_email' => $tenantUser->email,
        'tenant_phone' => '08031111111',
        'lease_start_date' => now()->toDateString(),
        'lease_end_date' => now()->addYear()->toDateString(),
        'rent_amount' => 2500000,
        'service_charge_amount' => 250000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    Livewire::actingAs($landlord)
        ->test(TenanciesIndex::class)
        ->set("updateStatus.{$tenancy->id}", TenancyStatus::Ended->value)
        ->set("updateLeaseStartDates.{$tenancy->id}", now()->subYear()->toDateString())
        ->set("updateLeaseEndDates.{$tenancy->id}", now()->toDateString())
        ->set("updateMoveInDates.{$tenancy->id}", now()->subYear()->toDateString())
        ->set("updateRentAmounts.{$tenancy->id}", '2500000')
        ->set("updateServiceCharges.{$tenancy->id}", '250000')
        ->set("updateBillingCycles.{$tenancy->id}", BillingCycle::Yearly->value)
        ->call('saveTenancy', $tenancy->id)
        ->assertHasNoErrors();

    expect($tenancy->fresh()->status)->toBe(TenancyStatus::Ended);
    expect($unit->fresh()->occupancy_status)->toBe(UnitOccupancyStatus::Vacant);
});

test('tenants only see their own tenancies', function () use ($makeAdmin, $makeLandlord, $makeTenant) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $tenantA = $makeTenant();
    $tenantB = $makeTenant();

    $property = Property::factory()->create([
        'title' => 'Harbor View',
        'landlord_id' => $landlord->landlordProfile->id,
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unitA = PropertyUnit::factory()->create(['property_id' => $property->id, 'occupancy_status' => UnitOccupancyStatus::Occupied]);
    $unitB = PropertyUnit::factory()->create(['property_id' => $property->id, 'occupancy_status' => UnitOccupancyStatus::Occupied]);

    Tenancy::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unitA->id,
        'tenant_user_id' => $tenantA->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
        'status' => TenancyStatus::Active,
        'tenant_name' => $tenantA->name,
        'tenant_email' => $tenantA->email,
        'tenant_phone' => '08032222222',
        'lease_start_date' => now()->subMonth()->toDateString(),
        'rent_amount' => 1800000,
        'service_charge_amount' => 150000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    Tenancy::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unitB->id,
        'tenant_user_id' => $tenantB->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
        'status' => TenancyStatus::Active,
        'tenant_name' => $tenantB->name,
        'tenant_email' => $tenantB->email,
        'tenant_phone' => '08033333333',
        'lease_start_date' => now()->subMonth()->toDateString(),
        'rent_amount' => 1800000,
        'service_charge_amount' => 150000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    $this->actingAs($tenantA)
        ->get(route('tenancies.index'))
        ->assertOk()
        ->assertSee($tenantA->name)
        ->assertDontSee($tenantB->name);
});

test('tenants see self service portal summary on tenancy page', function () use ($makeAdmin, $makeLandlord, $makeTenant) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $tenant = $makeTenant();

    $property = Property::factory()->create([
        'title' => 'Tenant Portal Towers',
        'landlord_id' => $landlord->landlordProfile->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'unit_name' => 'Suite TP1',
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
        'tenant_phone' => '08034444444',
        'lease_start_date' => now()->subMonth()->toDateString(),
        'lease_end_date' => now()->addDays(45)->toDateString(),
        'rent_amount' => 1800000,
        'service_charge_amount' => 150000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    Invoice::query()->create([
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'invoice_number' => 'INV-PORTAL-001',
        'invoice_type' => InvoiceType::Rent,
        'issue_date' => now()->subWeek()->toDateString(),
        'due_date' => now()->addWeek()->toDateString(),
        'subtotal_amount' => 1800000,
        'discount_amount' => 0,
        'total_amount' => 1800000,
        'balance_amount' => 900000,
        'status' => InvoiceStatus::PartiallyPaid,
        'issued_by' => $admin->id,
    ]);

    MaintenanceRequest::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'title' => 'Kitchen sink leak',
        'description' => 'Water drips under the cabinet.',
        'category' => 'plumbing',
        'priority' => MaintenancePriority::Medium,
        'status' => MaintenanceStatus::Open,
        'reported_at' => now(),
        'created_by' => $tenant->id,
        'updated_by' => $tenant->id,
    ]);

    $this->actingAs($tenant)
        ->get(route('tenancies.index'))
        ->assertOk()
        ->assertSee('Tenant portal')
        ->assertSee('Tenant Portal Towers')
        ->assertSee('Outstanding balance')
        ->assertSee('INV-PORTAL-001')
        ->assertSee('Kitchen sink leak');
});

test('landlords can upload tenancy documents and tenants can view them', function () use ($makeAdmin, $makeLandlord, $makeTenant) {
    $this->seed(RoleAndPermissionSeeder::class);
    Notification::fake();

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $tenant = $makeTenant();
    $otherTenant = $makeTenant();

    $property = Property::factory()->create([
        'title' => 'Lease Archive Court',
        'landlord_id' => $landlord->landlordProfile->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'unit_name' => 'Flat LA1',
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
        'tenant_phone' => '08035555555',
        'lease_start_date' => now()->subMonth()->toDateString(),
        'lease_end_date' => now()->addYear()->toDateString(),
        'rent_amount' => 1800000,
        'service_charge_amount' => 150000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    Livewire::actingAs($landlord)
        ->test(TenanciesIndex::class)
        ->set("documents.{$tenancy->id}", [
            UploadedFile::fake()->create('signed-lease.pdf', 120, 'application/pdf'),
        ])
        ->call('uploadDocuments', $tenancy->id)
        ->assertHasNoErrors();

    expect($tenancy->fresh()->getMedia('documents'))->toHaveCount(1);
    $document = $tenancy->fresh()->getFirstMedia('documents');

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'tenancy_documents_uploaded',
        'loggable_id' => $tenancy->id,
    ]);

    Notification::assertSentTo($tenant, TenancyDocumentAddedNotification::class);

    $this->actingAs($tenant)
        ->get(route('tenancies.documents.show', $document))
        ->assertOk();

    $this->actingAs($otherTenant)
        ->get(route('tenancies.documents.show', $document))
        ->assertForbidden();

    Livewire::actingAs($landlord)
        ->test(TenanciesIndex::class)
        ->set("documentLabels.{$document->id}", 'Signed lease agreement')
        ->call('renameDocument', $document->id)
        ->assertHasNoErrors();

    expect($document->fresh()->name)->toBe('Signed lease agreement');

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'tenancy_document_renamed',
        'loggable_id' => $tenancy->id,
    ]);

    $this->actingAs($tenant)
        ->get(route('tenancies.index'))
        ->assertOk()
        ->assertSee('Lease documents')
        ->assertSee('Signed lease agreement');

    Livewire::actingAs($landlord)
        ->test(TenanciesIndex::class)
        ->call('deleteDocument', $document->id)
        ->assertHasNoErrors();

    expect($document->fresh())->toBeNull();

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'tenancy_document_deleted',
        'loggable_id' => $tenancy->id,
    ]);
});
