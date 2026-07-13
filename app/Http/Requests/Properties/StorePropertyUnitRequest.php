<?php

namespace App\Http\Requests\Properties;

use App\Enums\BillingCycle;
use App\Enums\UnitOccupancyStatus;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Property $property */
        $property = $this->route('property');

        return $this->user()?->can('create', [\App\Models\PropertyUnit::class, $property]) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'unit_code' => ['nullable', 'string', 'max:100', 'unique:property_units,unit_code'],
            'unit_name' => ['required', 'string', 'max:255'],
            'unit_type' => ['nullable', 'string', 'max:120'],
            'floor_label' => ['nullable', 'string', 'max:120'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'toilets' => ['nullable', 'integer', 'min:0', 'max:50'],
            'size_sqm' => ['nullable', 'numeric', 'min:0'],
            'occupancy_status' => ['required', Rule::enum(UnitOccupancyStatus::class)],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', Rule::enum(BillingCycle::class)],
            'service_charge_amount' => ['nullable', 'numeric', 'min:0'],
            'caution_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'inspection_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'available_from' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'is_listed' => ['nullable', 'boolean'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:property_amenities,id'],
            'media' => ['nullable', 'array'],
            'media.*' => ['image', 'max:5120'],
        ];
    }
}
