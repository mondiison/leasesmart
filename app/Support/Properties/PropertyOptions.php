<?php

namespace App\Support\Properties;

use App\Enums\BillingCycle;
use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use App\Enums\UnitOccupancyStatus;
use App\Models\Caretaker;
use App\Models\Landlord;
use App\Models\PropertyAmenity;

class PropertyOptions
{
    /**
     * @return array<string, mixed>
     */
    public static function forForms(): array
    {
        return [
            'propertyTypes' => PropertyType::cases(),
            'publishStatuses' => PropertyPublishStatus::cases(),
            'billingCycles' => BillingCycle::cases(),
            'occupancyStatuses' => UnitOccupancyStatus::cases(),
            'landlords' => Landlord::query()->with('user')->orderBy('id')->get(),
            'caretakers' => Caretaker::query()->with('user')->orderBy('id')->get(),
            'amenities' => PropertyAmenity::query()->orderBy('name')->get(),
        ];
    }
}
