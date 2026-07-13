<?php

namespace App\Http\Requests\Properties;

use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Property $property */
        $property = $this->route('property');

        return $this->user()?->can('update', $property) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Property $property */
        $property = $this->route('property');

        return [
            'landlord_id' => ['nullable', 'integer', 'exists:landlords,id'],
            'caretaker_id' => ['nullable', 'integer', 'exists:caretakers,id'],
            'title' => ['required', 'string', 'max:255'],
            'property_code' => ['nullable', 'string', 'max:100', Rule::unique('properties', 'property_code')->ignore($property)],
            'property_type' => ['required', Rule::enum(PropertyType::class)],
            'description' => ['nullable', 'string'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['required', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'year_built' => ['nullable', 'integer', 'min:1800', 'max:'.(now()->year + 1)],
            'publish_status' => ['required', Rule::enum(PropertyPublishStatus::class)],
            'is_featured' => ['nullable', 'boolean'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:property_amenities,id'],
            'media' => ['nullable', 'array'],
            'media.*' => ['image', 'max:5120'],
        ];
    }
}
