<?php

namespace Database\Factories;

use App\Enums\InspectionStatus;
use App\Models\Inspection;
use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inspection>
 */
class InspectionFactory extends Factory
{
    protected $model = Inspection::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'property_unit_id' => PropertyUnit::factory(),
            'status' => InspectionStatus::Requested,
            'source' => 'marketplace',
            'requester_name' => fake()->name(),
            'requester_email' => fake()->safeEmail(),
            'requester_phone' => fake()->phoneNumber(),
            'requested_for_date' => now()->addDays(2)->toDateString(),
            'requested_for_time' => '10:00',
            'message' => fake()->sentence(),
            'internal_notes' => null,
        ];
    }
}
