<?php

namespace App\Support\Tenancies;

use App\Enums\BillingCycle;
use App\Enums\TenancyStatus;

class TenancyOptions
{
    /**
     * @return array{statuses: array<int, TenancyStatus>, billingCycles: array<int, BillingCycle>}
     */
    public static function forForms(): array
    {
        return [
            'statuses' => TenancyStatus::cases(),
            'billingCycles' => BillingCycle::cases(),
        ];
    }
}
