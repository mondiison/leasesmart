<?php

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use App\Enums\UnitOccupancyStatus;
use App\Models\Property;
use App\Models\PropertyAmenity;
use App\Models\PropertyUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DemoPropertySeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::query()->where('email', 'landlord@smartrent.test')->first();
        $caretaker = User::query()->where('email', 'caretaker@smartrent.test')->first();
        $admin = User::query()->where('email', 'admin@smartrent.test')->first();

        if ($landlord?->landlordProfile === null || $caretaker?->caretakerProfile === null || $admin === null) {
            return;
        }

        $amenities = PropertyAmenity::query()->get()->keyBy('slug');

        $portfolio = [
            [
                'title' => 'Maple Court Residences',
                'property_code' => 'SMR-MAPLE',
                'property_type' => PropertyType::ApartmentBuilding,
                'description' => 'A premium mid-rise rental property designed for young professionals and small families in central Lagos.',
                'address_line_1' => '12 Maple Crescent',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'postal_code' => '101241',
                'publish_status' => PropertyPublishStatus::Published,
                'is_featured' => true,
                'amenities' => ['24-7-security', 'backup-power', 'parking-space', 'fitted-kitchen', 'cctv-coverage', 'wi-fi-ready'],
                'units' => [
                    [
                        'unit_code' => 'MAP-A1',
                        'unit_name' => 'A1',
                        'unit_type' => 'Two Bedroom',
                        'floor_label' => 'First Floor',
                        'bedrooms' => 2,
                        'bathrooms' => 2,
                        'toilets' => 3,
                        'size_sqm' => 96,
                        'occupancy_status' => UnitOccupancyStatus::Vacant,
                        'rent_amount' => 2800000,
                        'billing_cycle' => BillingCycle::Yearly,
                        'service_charge_amount' => 320000,
                        'caution_fee_amount' => 200000,
                        'inspection_fee_amount' => 20000,
                        'available_from' => now()->addWeeks(2),
                        'description' => 'Bright two-bedroom corner unit with balcony and fitted kitchen.',
                        'is_listed' => true,
                        'amenities' => ['fitted-kitchen', 'wi-fi-ready'],
                    ],
                    [
                        'unit_code' => 'MAP-B3',
                        'unit_name' => 'B3',
                        'unit_type' => 'Three Bedroom',
                        'floor_label' => 'Second Floor',
                        'bedrooms' => 3,
                        'bathrooms' => 3,
                        'toilets' => 4,
                        'size_sqm' => 128,
                        'occupancy_status' => UnitOccupancyStatus::Occupied,
                        'rent_amount' => 3600000,
                        'billing_cycle' => BillingCycle::Yearly,
                        'service_charge_amount' => 420000,
                        'caution_fee_amount' => 250000,
                        'inspection_fee_amount' => 25000,
                        'available_from' => null,
                        'description' => 'Family-sized unit with service balcony and generous lounge.',
                        'is_listed' => false,
                        'amenities' => ['fitted-kitchen', 'smart-door-access'],
                    ],
                ],
            ],
            [
                'title' => 'Harbor View Apartments',
                'property_code' => 'SMR-HARBOR',
                'property_type' => PropertyType::MixedUse,
                'description' => 'A waterfront mixed-use address with serviced apartments and select live-work units.',
                'address_line_1' => '4 Admiralty Link Road',
                'city' => 'Lekki',
                'state' => 'Lagos',
                'postal_code' => '106104',
                'publish_status' => PropertyPublishStatus::UnderReview,
                'is_featured' => false,
                'amenities' => ['swimming-pool', 'gym', 'elevator', 'backup-power', 'cctv-coverage', 'rooftop-lounge'],
                'units' => [
                    [
                        'unit_code' => 'HV-201',
                        'unit_name' => '201',
                        'unit_type' => 'Studio',
                        'floor_label' => 'Second Floor',
                        'bedrooms' => 1,
                        'bathrooms' => 1,
                        'toilets' => 1,
                        'size_sqm' => 54,
                        'occupancy_status' => UnitOccupancyStatus::Reserved,
                        'rent_amount' => 1900000,
                        'billing_cycle' => BillingCycle::Yearly,
                        'service_charge_amount' => 260000,
                        'caution_fee_amount' => 150000,
                        'inspection_fee_amount' => 15000,
                        'available_from' => now()->addMonth(),
                        'description' => 'Compact serviced studio ideal for single professionals.',
                        'is_listed' => true,
                        'amenities' => ['elevator', 'gym'],
                    ],
                    [
                        'unit_code' => 'HV-305',
                        'unit_name' => '305',
                        'unit_type' => 'One Bedroom Loft',
                        'floor_label' => 'Third Floor',
                        'bedrooms' => 1,
                        'bathrooms' => 1,
                        'toilets' => 2,
                        'size_sqm' => 68,
                        'occupancy_status' => UnitOccupancyStatus::Vacant,
                        'rent_amount' => 2400000,
                        'billing_cycle' => BillingCycle::Yearly,
                        'service_charge_amount' => 300000,
                        'caution_fee_amount' => 180000,
                        'inspection_fee_amount' => 20000,
                        'available_from' => now()->addWeeks(3),
                        'description' => 'Loft-style one-bedroom with strong work-from-home appeal.',
                        'is_listed' => true,
                        'amenities' => ['rooftop-lounge', 'smart-door-access'],
                    ],
                ],
            ],
            [
                'title' => 'Palm Grove Terrace',
                'property_code' => 'SMR-PALM',
                'property_type' => PropertyType::Terrace,
                'description' => 'A compact gated terrace cluster focused on quiet long-term tenancy.',
                'address_line_1' => '18 Freedom Way Extension',
                'city' => 'Ibadan',
                'state' => 'Oyo',
                'postal_code' => '200285',
                'publish_status' => PropertyPublishStatus::Draft,
                'is_featured' => false,
                'amenities' => ['borehole-water', '24-7-security', 'parking-space', 'children-play-area'],
                'units' => [
                    [
                        'unit_code' => 'PG-T1',
                        'unit_name' => 'Terrace 1',
                        'unit_type' => 'Three Bedroom Terrace',
                        'floor_label' => 'Ground + First',
                        'bedrooms' => 3,
                        'bathrooms' => 3,
                        'toilets' => 4,
                        'size_sqm' => 142,
                        'occupancy_status' => UnitOccupancyStatus::UnderMaintenance,
                        'rent_amount' => 2200000,
                        'billing_cycle' => BillingCycle::Yearly,
                        'service_charge_amount' => 180000,
                        'caution_fee_amount' => 180000,
                        'inspection_fee_amount' => 10000,
                        'available_from' => now()->addMonths(2),
                        'description' => 'Corner terrace currently under finishing and repainting.',
                        'is_listed' => false,
                        'amenities' => ['children-play-area'],
                    ],
                    [
                        'unit_code' => 'PG-T2',
                        'unit_name' => 'Terrace 2',
                        'unit_type' => 'Three Bedroom Terrace',
                        'floor_label' => 'Ground + First',
                        'bedrooms' => 3,
                        'bathrooms' => 3,
                        'toilets' => 4,
                        'size_sqm' => 140,
                        'occupancy_status' => UnitOccupancyStatus::Vacant,
                        'rent_amount' => 2250000,
                        'billing_cycle' => BillingCycle::Yearly,
                        'service_charge_amount' => 180000,
                        'caution_fee_amount' => 180000,
                        'inspection_fee_amount' => 10000,
                        'available_from' => now()->addMonths(1),
                        'description' => 'Freshly turned-over terrace unit ready for viewing.',
                        'is_listed' => true,
                        'amenities' => ['parking-space', 'borehole-water'],
                    ],
                ],
            ],
        ];

        foreach ($portfolio as $data) {
            $property = Property::query()->updateOrCreate(
                ['property_code' => $data['property_code']],
                [
                    'landlord_id' => $landlord->landlordProfile->id,
                    'caretaker_id' => $caretaker->caretakerProfile->id,
                    'title' => $data['title'],
                    'slug' => Str::slug($data['title']),
                    'property_type' => $data['property_type'],
                    'description' => $data['description'],
                    'address_line_1' => $data['address_line_1'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'country' => 'Nigeria',
                    'postal_code' => $data['postal_code'],
                    'publish_status' => $data['publish_status'],
                    'is_featured' => $data['is_featured'],
                    'published_at' => $data['publish_status'] === PropertyPublishStatus::Published ? now()->subDays(5) : null,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ],
            );

            $property->amenities()->sync(
                collect($data['amenities'])
                    ->map(fn (string $slug) => $amenities->get($slug)?->id)
                    ->filter()
                    ->values()
                    ->all(),
            );

            foreach ($data['units'] as $unitData) {
                $unit = PropertyUnit::query()->updateOrCreate(
                    [
                        'property_id' => $property->id,
                        'unit_code' => $unitData['unit_code'],
                    ],
                    Arr::except($unitData, ['amenities']),
                );

                $unit->amenities()->sync(
                    collect($unitData['amenities'])
                        ->map(fn (string $slug) => $amenities->get($slug)?->id)
                        ->filter()
                        ->values()
                        ->all(),
                );
            }
        }
    }
}
