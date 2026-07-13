<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Enums\UnitOccupancyStatus;
use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyUnit>
 */
class PropertyUnitFactory extends Factory
{
    protected $model = PropertyUnit::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'unit_code' => 'UNIT-'.fake()->unique()->numberBetween(1000, 9999),
            'unit_name' => 'Unit '.fake()->unique()->numberBetween(1, 99),
            'unit_type' => fake()->randomElement(['Studio', 'Mini Flat', 'Two Bedroom', 'Three Bedroom']),
            'floor_label' => fake()->randomElement(['Ground Floor', 'First Floor', 'Second Floor']),
            'bedrooms' => fake()->numberBetween(1, 4),
            'bathrooms' => fake()->numberBetween(1, 4),
            'toilets' => fake()->numberBetween(1, 4),
            'size_sqm' => fake()->randomFloat(2, 25, 250),
            'occupancy_status' => UnitOccupancyStatus::Vacant,
            'rent_amount' => fake()->numberBetween(250000, 5000000),
            'billing_cycle' => BillingCycle::Yearly,
            'service_charge_amount' => fake()->numberBetween(0, 500000),
            'caution_fee_amount' => fake()->numberBetween(0, 300000),
            'inspection_fee_amount' => fake()->numberBetween(0, 100000),
            'available_from' => now()->addMonth(),
            'description' => fake()->sentence(),
            'is_listed' => true,
        ];
    }
}
