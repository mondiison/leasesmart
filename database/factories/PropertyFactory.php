<?php

namespace Database\Factories;

use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        $title = fake()->streetName().' Residences';

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'property_code' => 'PROP-'.fake()->unique()->numberBetween(1000, 9999),
            'property_type' => fake()->randomElement(PropertyType::cases()),
            'description' => fake()->paragraph(),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => 'Nigeria',
            'postal_code' => fake()->postcode(),
            'publish_status' => PropertyPublishStatus::Draft,
            'is_featured' => false,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
