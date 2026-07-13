<?php

use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use App\Enums\Role;
use App\Livewire\Properties\ManageProperty;
use App\Livewire\Properties\ManageUnit;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyAmenity;
use App\Models\PropertyUnit;
use App\Models\User;
use Database\Seeders\PropertyAmenitySeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

$makeAdmin = function (): User {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Admin->value);

    return $user;
};

$makeLandlord = function (): User {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Landlord->value);
    Landlord::query()->create(['user_id' => $user->id, 'company_name' => 'Acme Estates']);

    return $user;
};

test('admins can create properties with amenities and media', function () use ($makeAdmin, $makeLandlord) {
    Storage::fake('public');

    $this->seed([RoleAndPermissionSeeder::class, PropertyAmenitySeeder::class]);
    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $amenities = PropertyAmenity::query()->take(2)->get();

    Livewire::actingAs($admin)
        ->test(ManageProperty::class)
        ->set('landlord_id', $landlord->landlordProfile->id)
        ->set('title', 'Maple Court')
        ->set('property_code', 'MAP-001')
        ->set('property_type', PropertyType::ApartmentBuilding->value)
        ->set('description', 'Premium apartment portfolio.')
        ->set('address_line_1', '12 Maple Crescent')
        ->set('city', 'Lagos')
        ->set('state', 'Lagos')
        ->set('country', 'Nigeria')
        ->set('publish_status', PropertyPublishStatus::Draft->value)
        ->set('amenity_ids', $amenities->modelKeys())
        ->set('media', [UploadedFile::fake()->image('front.jpg')])
        ->call('save')
        ->assertHasNoErrors();

    $property = Property::query()->where('property_code', 'MAP-001')->firstOrFail();

    expect($property->amenities()->count())->toBe(2);
    expect($property->getMedia('gallery'))->toHaveCount(1);
    $this->assertDatabaseHas('activity_logs', [
        'action' => 'property_created',
        'loggable_id' => $property->id,
    ]);
});

test('landlords only see their assigned properties', function () use ($makeLandlord, $makeAdmin) {
    $this->seed([RoleAndPermissionSeeder::class, PropertyAmenitySeeder::class]);
    $admin = $makeAdmin();
    $landlordA = $makeLandlord();
    $landlordB = $makeLandlord();

    Property::factory()->create([
        'title' => 'Palm Heights',
        'landlord_id' => $landlordA->landlordProfile->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    Property::factory()->create([
        'title' => 'Harbor View',
        'landlord_id' => $landlordB->landlordProfile->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $this->actingAs($landlordA)
        ->get(route('properties.index'))
        ->assertOk()
        ->assertSee('Palm Heights')
        ->assertDontSee('Harbor View');
});

test('tenants cannot access property management', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $tenant = User::factory()->create(['email_verified_at' => now()]);
    $tenant->assignRole(Role::Tenant->value);

    $this->actingAs($tenant)
        ->get(route('properties.index'))
        ->assertForbidden();
});

test('properties cannot be published without an assigned landlord and listed unit', function () use ($makeAdmin) {
    $this->seed([RoleAndPermissionSeeder::class, PropertyAmenitySeeder::class]);
    $admin = $makeAdmin();

    $property = Property::factory()->create([
        'publish_status' => PropertyPublishStatus::Draft,
        'landlord_id' => null,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(ManageProperty::class, ['property' => $property])
        ->call('updatePublishStatus', PropertyPublishStatus::Published->value)
        ->assertHasErrors(['publish_status']);

    expect($property->fresh()->publish_status)->toBe(PropertyPublishStatus::Draft);
});

test('admins can clear optional property numeric fields while updating', function () use ($makeAdmin) {
    $this->seed([RoleAndPermissionSeeder::class, PropertyAmenitySeeder::class]);
    $admin = $makeAdmin();

    $property = Property::factory()->create([
        'title' => 'Coordinate House',
        'latitude' => '6.5243790',
        'longitude' => '3.3792060',
        'year_built' => 2020,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(ManageProperty::class, ['property' => $property])
        ->set('latitude', '')
        ->set('longitude', '')
        ->set('year_built', '')
        ->call('save')
        ->assertHasNoErrors();

    $property->refresh();

    expect($property->latitude)->toBeNull();
    expect($property->longitude)->toBeNull();
    expect($property->year_built)->toBeNull();
});

test('admins can choose and remove property gallery cover images', function () use ($makeAdmin) {
    Storage::fake('public');

    $this->seed([RoleAndPermissionSeeder::class, PropertyAmenitySeeder::class]);
    $admin = $makeAdmin();

    $property = Property::factory()->create([
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $first = $property->addMedia(UploadedFile::fake()->image('front.jpg'))->toMediaCollection('gallery');
    $second = $property->addMedia(UploadedFile::fake()->image('living.jpg'))->toMediaCollection('gallery');

    Livewire::actingAs($admin)
        ->test(ManageProperty::class, ['property' => $property])
        ->call('setGalleryCover', $second->id)
        ->assertHasNoErrors();

    expect($property->fresh()->getFirstMedia('gallery')->id)->toBe($second->id);

    Livewire::actingAs($admin)
        ->test(ManageProperty::class, ['property' => $property->fresh()])
        ->call('deleteGalleryImage', $first->id)
        ->assertHasNoErrors();

    expect($property->fresh()->getMedia('gallery')->pluck('id')->all())->toBe([$second->id]);
});

test('admins can choose unit gallery cover images', function () use ($makeAdmin) {
    Storage::fake('public');

    $this->seed([RoleAndPermissionSeeder::class, PropertyAmenitySeeder::class]);
    $admin = $makeAdmin();

    $property = Property::factory()->create([
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);
    $unit = PropertyUnit::factory()->create(['property_id' => $property->id]);

    $first = $unit->addMedia(UploadedFile::fake()->image('bedroom.jpg'))->toMediaCollection('gallery');
    $second = $unit->addMedia(UploadedFile::fake()->image('kitchen.jpg'))->toMediaCollection('gallery');

    Livewire::actingAs($admin)
        ->test(ManageUnit::class, ['property' => $property, 'unit' => $unit])
        ->call('setGalleryCover', $second->id)
        ->assertHasNoErrors();

    expect($unit->fresh()->getFirstMedia('gallery')->id)->toBe($second->id);
    expect($unit->fresh()->getMedia('gallery')->pluck('id')->all())->toBe([$second->id, $first->id]);
});

test('admins can publish properties after adding a listed unit', function () use ($makeAdmin, $makeLandlord) {
    $this->seed([RoleAndPermissionSeeder::class, PropertyAmenitySeeder::class]);
    $admin = $makeAdmin();
    $landlord = $makeLandlord();

    $property = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'publish_status' => PropertyPublishStatus::Draft,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'is_listed' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(ManageProperty::class, ['property' => $property])
        ->call('updatePublishStatus', PropertyPublishStatus::Published->value)
        ->assertHasNoErrors();

    expect($property->fresh()->publish_status)->toBe(PropertyPublishStatus::Published);
    expect($property->fresh()->published_at)->not->toBeNull();
});

test('admins can create and update property units', function () use ($makeAdmin, $makeLandlord) {
    Storage::fake('public');

    $this->seed([RoleAndPermissionSeeder::class, PropertyAmenitySeeder::class]);
    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $property = Property::factory()->create([
        'landlord_id' => $landlord->landlordProfile->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);
    $amenity = PropertyAmenity::query()->firstOrFail();

    Livewire::actingAs($admin)
        ->test(ManageUnit::class, ['property' => $property])
        ->set('unit_name', 'Block A - 02')
        ->set('unit_code', 'A-02')
        ->set('occupancy_status', 'vacant')
        ->set('rent_amount', '1800000')
        ->set('billing_cycle', 'yearly')
        ->set('is_listed', true)
        ->set('amenity_ids', [$amenity->id])
        ->set('media', [UploadedFile::fake()->image('unit.jpg')])
        ->call('save')
        ->assertHasNoErrors();

    $unit = $property->units()->firstOrFail();

    Livewire::actingAs($admin)
        ->test(ManageUnit::class, ['property' => $property, 'unit' => $unit])
        ->set('unit_name', 'Block A - 02 Deluxe')
        ->set('occupancy_status', 'reserved')
        ->set('rent_amount', '1950000')
        ->set('billing_cycle', 'yearly')
        ->set('service_charge_amount', '250000')
        ->set('amenity_ids', [])
        ->call('save')
        ->assertHasNoErrors();

    expect($unit->fresh()->unit_name)->toBe('Block A - 02 Deluxe');
    expect($unit->fresh()->amenities()->count())->toBe(0);
    expect($unit->getMedia('gallery'))->toHaveCount(1);
});
