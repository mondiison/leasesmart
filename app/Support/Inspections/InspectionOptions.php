<?php

namespace App\Support\Inspections;

use App\Enums\InspectionStatus;

class InspectionOptions
{
    /**
     * @return array{statuses: array<int, InspectionStatus>}
     */
    public static function forForms(): array
    {
        return [
            'statuses' => InspectionStatus::cases(),
        ];
    }
}
