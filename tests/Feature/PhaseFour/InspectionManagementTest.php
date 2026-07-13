<?php

use App\Enums\BillingCycle;
use App\Enums\InspectionStatus;
use App\Enums\PropertyPublishStatus;
use App\Enums\Role;
use App\Enums\UnitOccupancyStatus;
use App\Livewire\Inspections\Index as InspectionsIndex;
use App\Livewire\Marketplace\InspectionRequestForm;
use App\Models\Caretaker;
use App\Models\Inspection;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\User;
use App\Notifications\InspectionRequestReceivedNotification;
use App\Notifications\InspectionRequestedNotification;
use App\Notifications\InspectionStatusUpdatedNotification;
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
    Caretaker::query()->create(['user_id' => $user->id]);

    return $user;
};

$makeTenant = function (): User {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Tenant->value);

    return $user;
};

test('public visitors can request inspections for public units', function () use ($makeAdmin, $makeLandlord, $makeCaretaker) {
    Notification::fake();
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $caretaker = $makeCaretaker();

    $property = Property::factory()->create([
        'title' => 'Maple Court Residences',
        'slug' => 'maple-court-residences',
        'landlord_id' => $landlord->landlordProfile->id,
        'caretaker_id' => $caretaker->caretakerProfile->id,
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'unit_name' => 'A1',
        'occupancy_status' => UnitOccupancyStatus::Vacant,
        'billing_cycle' => BillingCycle::Yearly,
        'is_listed' => true,
    ]);

    Livewire::test(InspectionRequestForm::class, ['property' => $property])
        ->set('property_unit_id', $unit->id)
        ->set('requester_name', 'Jane Prospect')
        ->set('requester_email', 'jane@example.com')
        ->set('requester_phone', '08030000000')
        ->set('requested_for_date', now()->addDays(2)->toDateString())
        ->set('requested_for_time', '10:30')
        ->set('message', 'I would like a weekday morning visit.')
        ->call('save')
        ->assertHasNoErrors();

    $inspection = Inspection::query()->firstOrFail();

    expect($inspection->status)->toBe(InspectionStatus::Requested);
    expect($inspection->property_id)->toBe($property->id);
    expect($inspection->property_unit_id)->toBe($unit->id);

    Notification::assertSentTo($admin, InspectionRequestedNotification::class);
    Notification::assertSentTo($landlord, InspectionRequestedNotification::class);
    Notification::assertSentTo($caretaker, InspectionRequestedNotification::class);
    Notification::assertSentOnDemand(InspectionRequestReceivedNotification::class, function ($notification, array $channels, object $notifiable) {
        return in_array('mail', $channels, true)
            && $notifiable->routes['mail'] === 'jane@example.com';
    });

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'inspection_requested',
        'loggable_id' => $inspection->id,
    ]);
});

test('landlords only see inspections for their portfolio', function () use ($makeAdmin, $makeLandlord, $makeCaretaker) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlordA = $makeLandlord('Alpha Estates');
    $landlordB = $makeLandlord('Beta Estates');
    $caretaker = $makeCaretaker();

    $propertyA = Property::factory()->create([
        'title' => 'Palm Heights',
        'landlord_id' => $landlordA->landlordProfile->id,
        'caretaker_id' => $caretaker->caretakerProfile->id,
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $propertyB = Property::factory()->create([
        'title' => 'Harbor View',
        'landlord_id' => $landlordB->landlordProfile->id,
        'caretaker_id' => $caretaker->caretakerProfile->id,
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unitA = PropertyUnit::factory()->create(['property_id' => $propertyA->id, 'is_listed' => true, 'occupancy_status' => UnitOccupancyStatus::Vacant]);
    $unitB = PropertyUnit::factory()->create(['property_id' => $propertyB->id, 'is_listed' => true, 'occupancy_status' => UnitOccupancyStatus::Vacant]);

    Inspection::query()->create([
        'property_id' => $propertyA->id,
        'property_unit_id' => $unitA->id,
        'status' => InspectionStatus::Requested,
        'source' => 'marketplace',
        'requester_name' => 'Alice',
        'requester_email' => 'alice@example.com',
        'requester_phone' => '08031111111',
    ]);

    Inspection::query()->create([
        'property_id' => $propertyB->id,
        'property_unit_id' => $unitB->id,
        'status' => InspectionStatus::Requested,
        'source' => 'marketplace',
        'requester_name' => 'Bob',
        'requester_email' => 'bob@example.com',
        'requester_phone' => '08032222222',
    ]);

    $this->actingAs($landlordA)
        ->get(route('inspections.index'))
        ->assertOk()
        ->assertSee('Palm Heights')
        ->assertDontSee('Harbor View');
});

test('admins can update inspection statuses and notify the requester', function () use ($makeAdmin, $makeLandlord, $makeCaretaker, $makeTenant) {
    Notification::fake();
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $caretaker = $makeCaretaker();
    $requester = $makeTenant();

    $property = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'caretaker_id' => $caretaker->caretakerProfile->id,
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'is_listed' => true,
        'occupancy_status' => UnitOccupancyStatus::Vacant,
    ]);

    $inspection = Inspection::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'requester_user_id' => $requester->id,
        'status' => InspectionStatus::Requested,
        'source' => 'marketplace',
        'requester_name' => $requester->name,
        'requester_email' => $requester->email,
        'requester_phone' => '08033333333',
    ]);

    $scheduledAt = now()->addDays(3)->setTime(14, 0)->format('Y-m-d\TH:i');

    Livewire::actingAs($admin)
        ->test(InspectionsIndex::class)
        ->set("statusUpdates.{$inspection->id}", InspectionStatus::Confirmed->value)
        ->set("scheduledAts.{$inspection->id}", $scheduledAt)
        ->set("internalNotes.{$inspection->id}", 'Please arrive 10 minutes early.')
        ->call('save', $inspection->id)
        ->assertHasNoErrors();

    expect($inspection->fresh()->status)->toBe(InspectionStatus::Confirmed);
    expect($inspection->fresh()->handled_by)->toBe($admin->id);
    expect($inspection->fresh()->scheduled_at)->not->toBeNull();

    Notification::assertSentTo($requester, InspectionStatusUpdatedNotification::class);

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'inspection_updated',
        'loggable_id' => $inspection->id,
    ]);
});

test('tenants cannot access the inspections management queue', function () use ($makeTenant) {
    $this->seed(RoleAndPermissionSeeder::class);

    $tenant = $makeTenant();

    $this->actingAs($tenant)
        ->get(route('inspections.index'))
        ->assertForbidden();
});
