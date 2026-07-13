<?php

use App\Enums\BillingCycle;
use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use App\Enums\UnitOccupancyStatus;
use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('shows only published properties with public units on the marketplace', function () {
    $published = Property::factory()->create([
        'title' => 'Maple Court Residences',
        'slug' => 'maple-court-residences',
        'city' => 'Lagos',
        'state' => 'Lagos',
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    PropertyUnit::factory()->create([
        'property_id' => $published->id,
        'unit_name' => 'A1',
        'unit_type' => 'Two Bedroom',
        'bedrooms' => 2,
        'rent_amount' => 2800000,
        'billing_cycle' => BillingCycle::Yearly,
        'occupancy_status' => UnitOccupancyStatus::Vacant,
        'is_listed' => true,
    ]);

    $draft = Property::factory()->create([
        'title' => 'Palm Grove Terrace',
        'slug' => 'palm-grove-terrace',
        'city' => 'Ibadan',
        'state' => 'Oyo',
        'publish_status' => PropertyPublishStatus::Draft,
    ]);

    PropertyUnit::factory()->create([
        'property_id' => $draft->id,
        'unit_name' => 'T1',
        'occupancy_status' => UnitOccupancyStatus::Vacant,
        'is_listed' => true,
    ]);

    $hiddenByUnit = Property::factory()->create([
        'title' => 'Harbor View Apartments',
        'slug' => 'harbor-view-apartments',
        'city' => 'Lekki',
        'state' => 'Lagos',
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subHours(8),
    ]);

    PropertyUnit::factory()->create([
        'property_id' => $hiddenByUnit->id,
        'unit_name' => '305',
        'occupancy_status' => UnitOccupancyStatus::Occupied,
        'is_listed' => true,
    ]);

    $this->get(route('marketplace.index'))
        ->assertOk()
        ->assertSee('Maple Court Residences')
        ->assertDontSee('Palm Grove Terrace')
        ->assertDontSee('Harbor View Apartments');
});

it('filters marketplace listings by city, bedroom count, and budget', function () {
    $lagosProperty = Property::factory()->create([
        'title' => 'Cedar Heights',
        'slug' => 'cedar-heights',
        'city' => 'Lagos',
        'state' => 'Lagos',
        'publish_status' => PropertyPublishStatus::Published,
        'property_type' => PropertyType::ApartmentBuilding,
        'published_at' => now()->subDay(),
    ]);

    PropertyUnit::factory()->create([
        'property_id' => $lagosProperty->id,
        'unit_name' => 'Pent 1',
        'bedrooms' => 3,
        'rent_amount' => 3200000,
        'occupancy_status' => UnitOccupancyStatus::Vacant,
        'is_listed' => true,
    ]);

    $abujaProperty = Property::factory()->create([
        'title' => 'Oak Terrace',
        'slug' => 'oak-terrace',
        'city' => 'Abuja',
        'state' => 'FCT',
        'publish_status' => PropertyPublishStatus::Published,
        'property_type' => PropertyType::Terrace,
        'published_at' => now()->subHours(12),
    ]);

    PropertyUnit::factory()->create([
        'property_id' => $abujaProperty->id,
        'unit_name' => 'T2',
        'bedrooms' => 2,
        'rent_amount' => 1800000,
        'occupancy_status' => UnitOccupancyStatus::Vacant,
        'is_listed' => true,
    ]);

    $this->get(route('marketplace.index', [
        'city' => 'Abuja',
        'bedrooms' => 2,
        'max_rent' => 2000000,
    ]))
        ->assertOk()
        ->assertSee('Oak Terrace')
        ->assertDontSee('Cedar Heights');
});

it('serves marketplace detail pages by slug and blocks non-public properties', function () {
    $publicProperty = Property::factory()->create([
        'title' => 'Seabreeze Court',
        'slug' => 'seabreeze-court',
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    PropertyUnit::factory()->create([
        'property_id' => $publicProperty->id,
        'unit_name' => 'Suite 4',
        'occupancy_status' => UnitOccupancyStatus::Reserved,
        'is_listed' => true,
    ]);

    $privateProperty = Property::factory()->create([
        'title' => 'Internal Portfolio Asset',
        'slug' => 'internal-portfolio-asset',
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    PropertyUnit::factory()->create([
        'property_id' => $privateProperty->id,
        'unit_name' => 'Hidden 1',
        'occupancy_status' => UnitOccupancyStatus::Occupied,
        'is_listed' => true,
    ]);

    $this->get(route('marketplace.show', $publicProperty))
        ->assertOk()
        ->assertSee('Seabreeze Court')
        ->assertSee('Suite 4');

    $this->get(route('marketplace.show', $privateProperty))
        ->assertNotFound();
});

it('shows ordered property and unit gallery sliders on detail pages', function () {
    Storage::fake('public');

    $property = Property::factory()->create([
        'title' => 'Gallery Heights',
        'slug' => 'gallery-heights',
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'unit_name' => 'Suite Gallery',
        'occupancy_status' => UnitOccupancyStatus::Vacant,
        'is_listed' => true,
    ]);

    $property->addMedia(UploadedFile::fake()->image('property-cover.jpg'))->toMediaCollection('gallery');
    $property->addMedia(UploadedFile::fake()->image('property-second.jpg'))->toMediaCollection('gallery');
    $unit->addMedia(UploadedFile::fake()->image('unit-cover.jpg'))->toMediaCollection('gallery');
    $unit->addMedia(UploadedFile::fake()->image('unit-second.jpg'))->toMediaCollection('gallery');

    $this->get(route('marketplace.show', $property))
        ->assertOk()
        ->assertSee('1 / 2')
        ->assertSee('Show property image 2')
        ->assertSee('Show Suite Gallery image 2');
});
