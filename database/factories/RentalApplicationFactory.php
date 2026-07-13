<?php

namespace Database\Factories;

use App\Enums\RentalApplicationStatus;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\RentalApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentalApplication>
 */
class RentalApplicationFactory extends Factory
{
    protected $model = RentalApplication::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'property_unit_id' => PropertyUnit::factory(),
            'status' => RentalApplicationStatus::Submitted,
            'source' => 'marketplace',
            'applicant_name' => fake()->name(),
            'applicant_email' => fake()->safeEmail(),
            'applicant_phone' => fake()->phoneNumber(),
            'employment_status' => fake()->randomElement(['Employed', 'Self-employed', 'Student']),
            'employer_name' => fake()->company(),
            'monthly_income' => fake()->numberBetween(150000, 1200000),
            'preferred_move_in_date' => now()->addWeeks(3)->toDateString(),
            'message' => fake()->sentence(),
            'submitted_at' => now(),
        ];
    }
}
