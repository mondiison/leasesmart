<?php

namespace App\Actions\Tenancies;

use App\Actions\Activity\LogActivityAction;
use App\Enums\TenancyStatus;
use App\Models\Tenancy;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateTenancyAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(User $actor, Tenancy $tenancy, array $payload): Tenancy
    {
        return DB::transaction(function () use ($actor, $tenancy, $payload): Tenancy {
            $status = TenancyStatus::from($payload['status']);

            $tenancy->fill([
                'status' => $status,
                'lease_start_date' => $payload['lease_start_date'],
                'lease_end_date' => $payload['lease_end_date'] ?? null,
                'move_in_date' => $payload['move_in_date'] ?? null,
                'rent_amount' => $payload['rent_amount'],
                'service_charge_amount' => $payload['service_charge_amount'] ?? 0,
                'billing_cycle' => $payload['billing_cycle'],
                'notes' => $payload['notes'] ?? null,
                'updated_by' => $actor->getKey(),
                'activated_at' => $status === TenancyStatus::Active && $tenancy->activated_at === null ? now() : $tenancy->activated_at,
                'ended_at' => $status === TenancyStatus::Ended ? now() : null,
            ])->save();

            $tenancy->load('unit');
            $tenancy->applyUnitOccupancy();

            $this->logActivity->execute(
                user: $actor,
                action: 'tenancy_updated',
                description: "Updated tenancy #{$tenancy->getKey()} to {$tenancy->status->label()}.",
                subject: $tenancy,
                metadata: ['status' => $tenancy->status->value],
            );

            return $tenancy;
        });
    }
}
