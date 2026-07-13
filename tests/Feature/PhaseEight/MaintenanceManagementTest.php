<?php

use App\Enums\BillingCycle;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Enums\PropertyPublishStatus;
use App\Enums\Role;
use App\Enums\TenancyStatus;
use App\Enums\UnitOccupancyStatus;
use App\Livewire\Maintenance\Index as MaintenanceIndex;
use App\Models\Caretaker;
use App\Models\Landlord;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\Tenancy;
use App\Models\User;
use App\Notifications\MaintenanceRequestCreatedNotification;
use App\Notifications\MaintenanceRequestUpdatedNotification;
use Database\Seeders\RoleAndPermissionSeeder;
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

$makeCaretaker = function (): User {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Caretaker->value);
    Caretaker::query()->create(['user_id' => $user->id, 'employee_code' => 'CT-001']);

    return $user;
};

$makeTenant = function (): User {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Tenant->value);
    Tenant::query()->create(['user_id' => $user->id, 'full_name' => $user->name, 'email' => $user->email]);

    return $user;
};

$makeTenancy = function (User $admin, User $landlord, User $tenant, ?User $caretaker = null, string $title = 'Maple Court'): Tenancy {
    $property = Property::factory()->create([
        'title' => $title,
        'landlord_id' => $landlord->landlordProfile->id,
        'caretaker_id' => $caretaker?->caretakerProfile?->id,
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'occupancy_status' => UnitOccupancyStatus::Occupied,
        'billing_cycle' => BillingCycle::Yearly,
        'is_listed' => true,
    ]);

    return Tenancy::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'tenant_user_id' => $tenant->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
        'status' => TenancyStatus::Active,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'tenant_phone' => '08030000000',
        'lease_start_date' => now()->subMonth()->toDateString(),
        'lease_end_date' => now()->addYear()->toDateString(),
        'rent_amount' => 2500000,
        'service_charge_amount' => 250000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);
};

test('tenants can create maintenance requests for their tenancy', function () use ($makeAdmin, $makeLandlord, $makeTenant, $makeCaretaker, $makeTenancy) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $caretaker = $makeCaretaker();
    $tenant = $makeTenant();
    $tenancy = $makeTenancy($admin, $landlord, $tenant, $caretaker);

    Notification::fake();

    Livewire::actingAs($tenant)
        ->test(MaintenanceIndex::class)
        ->set('tenancy_id', $tenancy->id)
        ->set('property_id', $tenancy->property_id)
        ->set('property_unit_id', $tenancy->property_unit_id)
        ->set('title', 'Leaking bathroom tap')
        ->set('description', 'The bathroom tap has been leaking since yesterday.')
        ->set('category', 'plumbing')
        ->set('priority', MaintenancePriority::High->value)
        ->call('createRequest')
        ->assertHasNoErrors();

    $request = MaintenanceRequest::query()->firstOrFail();

    expect($request->tenant_user_id)->toBe($tenant->id)
        ->and($request->status)->toBe(MaintenanceStatus::Open)
        ->and($request->updates)->toHaveCount(1);

    Notification::assertSentTo($landlord, MaintenanceRequestCreatedNotification::class);
    Notification::assertSentTo($caretaker, MaintenanceRequestCreatedNotification::class);

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'maintenance_request_created',
        'loggable_id' => $request->id,
    ]);

    $this->actingAs($tenant)
        ->get(route('maintenance.index'))
        ->assertOk()
        ->assertSee('Maintenance Support')
        ->assertSee('My Support Requests')
        ->assertSee('Leaking bathroom tap');
});

test('caretakers can update and resolve assigned maintenance requests', function () use ($makeAdmin, $makeLandlord, $makeTenant, $makeCaretaker, $makeTenancy) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $caretaker = $makeCaretaker();
    $tenant = $makeTenant();
    $tenancy = $makeTenancy($admin, $landlord, $tenant, $caretaker, 'Palm Grove');

    $request = MaintenanceRequest::query()->create([
        'property_id' => $tenancy->property_id,
        'property_unit_id' => $tenancy->property_unit_id,
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'title' => 'Power outage in living room',
        'description' => 'Sockets are not working.',
        'category' => 'electrical',
        'priority' => MaintenancePriority::Urgent,
        'status' => MaintenanceStatus::Open,
        'reported_at' => now(),
        'assigned_to' => $caretaker->id,
        'created_by' => $tenant->id,
        'updated_by' => $tenant->id,
    ]);

    $request->updates()->create([
        'user_id' => $tenant->id,
        'status' => MaintenanceStatus::Open,
        'message' => 'Initial request submitted.',
    ]);

    Notification::fake();

    Livewire::actingAs($caretaker)
        ->test(MaintenanceIndex::class)
        ->set("statusUpdates.{$request->id}", MaintenanceStatus::Resolved->value)
        ->set("messages.{$request->id}", 'Faulty breaker replaced and power restored.')
        ->set("assigneeUpdates.{$request->id}", $caretaker->id)
        ->call('addUpdate', $request->id)
        ->assertHasNoErrors();

    $request->refresh();

    expect($request->status)->toBe(MaintenanceStatus::Resolved)
        ->and($request->updates()->count())->toBe(2)
        ->and($request->resolved_at)->not->toBeNull();

    Notification::assertSentTo($tenant, MaintenanceRequestUpdatedNotification::class);
});

test('tenants can confirm or reopen resolved maintenance requests', function () use ($makeAdmin, $makeLandlord, $makeTenant, $makeCaretaker, $makeTenancy) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $caretaker = $makeCaretaker();
    $tenant = $makeTenant();
    $tenancy = $makeTenancy($admin, $landlord, $tenant, $caretaker, 'Azure Place');

    $request = MaintenanceRequest::query()->create([
        'property_id' => $tenancy->property_id,
        'property_unit_id' => $tenancy->property_unit_id,
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'title' => 'Broken water heater',
        'description' => 'No hot water in the bathroom.',
        'category' => 'plumbing',
        'priority' => MaintenancePriority::Medium,
        'status' => MaintenanceStatus::Resolved,
        'reported_at' => now()->subDay(),
        'assigned_to' => $caretaker->id,
        'created_by' => $tenant->id,
        'updated_by' => $caretaker->id,
        'resolved_at' => now(),
    ]);

    Notification::fake();

    Livewire::actingAs($tenant)
        ->test(MaintenanceIndex::class)
        ->set("tenantResolutionNotes.{$request->id}", 'Confirmed, hot water is back.')
        ->call('confirmResolution', $request->id)
        ->assertHasNoErrors();

    expect($request->fresh()->status)->toBe(MaintenanceStatus::Closed);

    $request = $request->fresh();

    $request->forceFill([
        'status' => MaintenanceStatus::Resolved,
        'closed_at' => null,
        'resolved_at' => now(),
    ])->save();

    Livewire::actingAs($tenant)
        ->test(MaintenanceIndex::class)
        ->set("tenantResolutionNotes.{$request->id}", 'The water heater stopped again.')
        ->call('reopenRequest', $request->id)
        ->assertHasNoErrors();

    expect($request->fresh()->status)->toBe(MaintenanceStatus::Open);
});

test('landlords only see maintenance requests for their portfolio', function () use ($makeAdmin, $makeLandlord, $makeTenant, $makeTenancy) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlordA = $makeLandlord('North Star');
    $landlordB = $makeLandlord('South Star');
    $tenantA = $makeTenant();
    $tenantB = $makeTenant();

    $tenancyA = $makeTenancy($admin, $landlordA, $tenantA, null, 'North Star Court');
    $tenancyB = $makeTenancy($admin, $landlordB, $tenantB, null, 'South Star Court');

    MaintenanceRequest::query()->create([
        'property_id' => $tenancyA->property_id,
        'property_unit_id' => $tenancyA->property_unit_id,
        'tenancy_id' => $tenancyA->id,
        'tenant_user_id' => $tenantA->id,
        'title' => 'North leak',
        'description' => 'North portfolio issue',
        'priority' => MaintenancePriority::Medium,
        'status' => MaintenanceStatus::Open,
        'reported_at' => now(),
        'created_by' => $tenantA->id,
        'updated_by' => $tenantA->id,
    ]);

    MaintenanceRequest::query()->create([
        'property_id' => $tenancyB->property_id,
        'property_unit_id' => $tenancyB->property_unit_id,
        'tenancy_id' => $tenancyB->id,
        'tenant_user_id' => $tenantB->id,
        'title' => 'South leak',
        'description' => 'South portfolio issue',
        'priority' => MaintenancePriority::Medium,
        'status' => MaintenanceStatus::Open,
        'reported_at' => now(),
        'created_by' => $tenantB->id,
        'updated_by' => $tenantB->id,
    ]);

    $this->actingAs($landlordA)
        ->get(route('maintenance.index'))
        ->assertOk()
        ->assertSee('North leak')
        ->assertDontSee('South leak');
});
