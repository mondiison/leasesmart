<?php

namespace App\Actions\Tenancies;

use App\Actions\Activity\LogActivityAction;
use App\Enums\RentalApplicationStatus;
use App\Enums\TenancyStatus;
use App\Models\RentalApplication;
use App\Models\Tenancy;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class CreateTenancyAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(User $actor, RentalApplication $application, array $payload): Tenancy
    {
        if ($application->status !== RentalApplicationStatus::Approved) {
            throw new DomainException('Only approved applications can be converted into tenancies.');
        }

        if ($application->tenancy()->exists()) {
            throw new DomainException('This application has already been converted into a tenancy.');
        }

        return DB::transaction(function () use ($actor, $application, $payload): Tenancy {
            $unit = $application->unit()->lockForUpdate()->firstOrFail();

            if ($unit->occupancy_status->value === 'occupied') {
                throw new DomainException('This unit is already occupied and cannot accept a new tenancy.');
            }

            $tenancy = Tenancy::query()->create([
                'property_id' => $application->property_id,
                'property_unit_id' => $application->property_unit_id,
                'rental_application_id' => $application->getKey(),
                'tenant_user_id' => $application->applicant_user_id,
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
                'status' => $payload['status'],
                'tenant_name' => $application->applicant_name,
                'tenant_email' => $application->applicant_email,
                'tenant_phone' => $application->applicant_phone,
                'lease_start_date' => $payload['lease_start_date'],
                'lease_end_date' => $payload['lease_end_date'] ?? null,
                'move_in_date' => $payload['move_in_date'] ?? null,
                'activated_at' => $payload['status'] === TenancyStatus::Active->value ? now() : null,
                'rent_amount' => $payload['rent_amount'] ?? $unit->rent_amount,
                'service_charge_amount' => $payload['service_charge_amount'] ?? $unit->service_charge_amount,
                'billing_cycle' => $payload['billing_cycle'] ?? $unit->billing_cycle->value,
                'notes' => $payload['notes'] ?? null,
            ]);

            $application->forceFill([
                'status' => RentalApplicationStatus::Converted,
                'reviewed_by' => $actor->getKey(),
                'decided_at' => now(),
            ])->save();

            $tenancy->load('unit');
            $tenancy->applyUnitOccupancy();

            $this->logActivity->execute(
                user: $actor,
                action: 'tenancy_created',
                description: "Created tenancy for {$tenancy->tenant_name} at {$tenancy->property->title}.",
                subject: $tenancy,
                metadata: [
                    'rental_application_id' => $application->getKey(),
                    'status' => $tenancy->status->value,
                ],
            );

            return $tenancy;
        });
    }
}
