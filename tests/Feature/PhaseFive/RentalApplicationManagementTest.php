<?php

use App\Enums\BillingCycle;
use App\Enums\PropertyPublishStatus;
use App\Enums\RentalApplicationStatus;
use App\Enums\Role;
use App\Enums\UnitOccupancyStatus;
use App\Livewire\Applications\Index as ApplicationsIndex;
use App\Livewire\Marketplace\RentalApplicationForm;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\RentalApplication;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\RentalApplicationReceivedNotification;
use App\Notifications\RentalApplicationStatusUpdatedNotification;
use App\Notifications\RentalApplicationSubmittedNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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

test('public visitors can submit rental applications with documents', function () use ($makeAdmin, $makeLandlord) {
    Storage::fake('public');
    Notification::fake();
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();

    $property = Property::factory()->create([
        'title' => 'Maple Court Residences',
        'slug' => 'maple-court-residences',
        'landlord_id' => $landlord->landlordProfile->id,
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

    Livewire::test(RentalApplicationForm::class, ['property' => $property])
        ->set('property_unit_id', $unit->id)
        ->set('applicant_name', 'Jane Prospect')
        ->set('applicant_email', 'jane@example.com')
        ->set('applicant_phone', '08030000000')
        ->set('employment_status', 'Employed')
        ->set('employer_name', 'LeaseSmart Demo')
        ->set('monthly_income', '450000')
        ->set('preferred_move_in_date', now()->addWeeks(3)->toDateString())
        ->set('message', 'Ready to move quickly if approved.')
        ->set('documents', [UploadedFile::fake()->create('pay-slip.pdf', 120, 'application/pdf')])
        ->call('save')
        ->assertHasNoErrors();

    $application = RentalApplication::query()->firstOrFail();

    expect($application->status)->toBe(RentalApplicationStatus::Submitted);
    expect($application->property_id)->toBe($property->id);
    expect($application->property_unit_id)->toBe($unit->id);
    expect($application->getMedia('documents'))->toHaveCount(1);

    Notification::assertSentTo($admin, RentalApplicationSubmittedNotification::class);
    Notification::assertSentTo($landlord, RentalApplicationSubmittedNotification::class);
    Notification::assertSentOnDemand(RentalApplicationReceivedNotification::class, function ($notification, array $channels, object $notifiable) {
        return in_array('mail', $channels, true)
            && $notifiable->routes['mail'] === 'jane@example.com';
    });

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'rental_application_submitted',
        'loggable_id' => $application->id,
    ]);
});

test('landlords only see rental applications for their portfolio', function () use ($makeAdmin, $makeLandlord) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlordA = $makeLandlord('Alpha Estates');
    $landlordB = $makeLandlord('Beta Estates');

    $propertyA = Property::factory()->create([
        'title' => 'Palm Heights',
        'landlord_id' => $landlordA->landlordProfile->id,
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $propertyB = Property::factory()->create([
        'title' => 'Harbor View',
        'landlord_id' => $landlordB->landlordProfile->id,
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unitA = PropertyUnit::factory()->create(['property_id' => $propertyA->id, 'is_listed' => true, 'occupancy_status' => UnitOccupancyStatus::Vacant]);
    $unitB = PropertyUnit::factory()->create(['property_id' => $propertyB->id, 'is_listed' => true, 'occupancy_status' => UnitOccupancyStatus::Vacant]);

    RentalApplication::query()->create([
        'property_id' => $propertyA->id,
        'property_unit_id' => $unitA->id,
        'status' => RentalApplicationStatus::Submitted,
        'source' => 'marketplace',
        'applicant_name' => 'Alice',
        'applicant_email' => 'alice@example.com',
        'applicant_phone' => '08031111111',
        'submitted_at' => now(),
    ]);

    RentalApplication::query()->create([
        'property_id' => $propertyB->id,
        'property_unit_id' => $unitB->id,
        'status' => RentalApplicationStatus::Submitted,
        'source' => 'marketplace',
        'applicant_name' => 'Bob',
        'applicant_email' => 'bob@example.com',
        'applicant_phone' => '08032222222',
        'submitted_at' => now(),
    ]);

    $this->actingAs($landlordA)
        ->get(route('applications.index'))
        ->assertOk()
        ->assertSee('Palm Heights')
        ->assertDontSee('Harbor View');
});

test('admins can update rental application statuses and notify the applicant', function () use ($makeAdmin, $makeLandlord, $makeTenant) {
    Notification::fake();
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $applicant = $makeTenant();

    $property = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
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

    $application = RentalApplication::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'applicant_user_id' => $applicant->id,
        'status' => RentalApplicationStatus::Submitted,
        'source' => 'marketplace',
        'applicant_name' => $applicant->name,
        'applicant_email' => $applicant->email,
        'applicant_phone' => '08033333333',
        'submitted_at' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(ApplicationsIndex::class)
        ->set("statusUpdates.{$application->id}", RentalApplicationStatus::Approved->value)
        ->set("reviewNotes.{$application->id}", 'Income and timeline look good.')
        ->set("agentFeeAmounts.{$application->id}", '150000')
        ->set("legalFeeAmounts.{$application->id}", '75000')
        ->call('save', $application->id)
        ->assertHasNoErrors();

    expect($application->fresh()->status)->toBe(RentalApplicationStatus::Approved);
    expect($application->fresh()->reviewed_by)->toBe($admin->id);
    expect($application->fresh()->decided_at)->not->toBeNull();
    expect($application->fresh()->agent_fee_amount)->toBe('150000.00');
    expect($application->fresh()->legal_fee_amount)->toBe('75000.00');

    Notification::assertSentTo($applicant, RentalApplicationStatusUpdatedNotification::class);

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'rental_application_updated',
        'loggable_id' => $application->id,
    ]);
});

test('tenants can view their own rental applications and statuses', function () use ($makeAdmin, $makeLandlord, $makeTenant) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $tenant = $makeTenant();
    $otherTenant = $makeTenant();

    $property = Property::factory()->create([
        'title' => 'Tenant Visible Heights',
        'landlord_id' => $landlord->landlordProfile->id,
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'unit_name' => 'B2',
        'is_listed' => true,
        'occupancy_status' => UnitOccupancyStatus::Vacant,
    ]);

    RentalApplication::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'applicant_user_id' => $tenant->id,
        'status' => RentalApplicationStatus::Approved,
        'source' => 'marketplace',
        'applicant_name' => $tenant->name,
        'applicant_email' => $tenant->email,
        'applicant_phone' => '08034444444',
        'review_notes' => 'Approved pending payment.',
        'agent_fee_amount' => 120000,
        'legal_fee_amount' => 60000,
        'submitted_at' => now(),
    ]);

    RentalApplication::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'applicant_user_id' => $otherTenant->id,
        'status' => RentalApplicationStatus::Submitted,
        'source' => 'marketplace',
        'applicant_name' => 'Other Applicant',
        'applicant_email' => $otherTenant->email,
        'applicant_phone' => '08035555555',
        'submitted_at' => now(),
    ]);

    $this->actingAs($tenant)
        ->get(route('applications.index'))
        ->assertOk()
        ->assertSee('My Rental Applications')
        ->assertSee('Tenant Visible Heights')
        ->assertSee('Approved')
        ->assertSee('Approved pending payment.')
        ->assertSee('120,000.00')
        ->assertSee('60,000.00')
        ->assertDontSee('Other Applicant');
});
