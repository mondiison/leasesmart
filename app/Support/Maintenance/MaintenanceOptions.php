<?php

namespace App\Support\Maintenance;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\User;

class MaintenanceOptions
{
    public static function for(User $user): array
    {
        return [
            'priorities' => MaintenancePriority::cases(),
            'statuses' => MaintenanceStatus::cases(),
            'categories' => [
                'plumbing',
                'electrical',
                'hvac',
                'structural',
                'appliance',
                'cleaning',
                'security',
                'general',
            ],
            'properties' => Property::query()->with('units')->visibleTo($user)->orderBy('title')->get(),
            'tenancies' => Tenancy::query()->with(['property', 'unit'])->visibleTo($user)->latest('lease_start_date')->get(),
            'assignableUsers' => User::query()->role(['admin', 'caretaker'])->orderBy('name')->get(),
        ];
    }
}
