<?php

namespace App\Support\Applications;

use App\Enums\RentalApplicationStatus;

class RentalApplicationOptions
{
    /**
     * @return array{statuses: array<int, RentalApplicationStatus>, employmentStatuses: array<int, string>}
     */
    public static function forForms(): array
    {
        return [
            'statuses' => RentalApplicationStatus::cases(),
            'employmentStatuses' => [
                'Employed',
                'Self-employed',
                'Business Owner',
                'Student',
                'Unemployed',
                'Retired',
            ],
        ];
    }
}
