<?php

namespace App\Actions\Applications;

use App\Actions\Activity\LogActivityAction;
use App\Enums\RentalApplicationStatus;
use App\Models\RentalApplication;
use App\Models\User;
use App\Notifications\RentalApplicationStatusUpdatedNotification;
use Illuminate\Support\Facades\DB;

class UpdateRentalApplicationAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(User $actor, RentalApplication $application, array $payload): RentalApplication
    {
        return DB::transaction(function () use ($actor, $application, $payload): RentalApplication {
            $status = RentalApplicationStatus::from($payload['status']);

            $application->fill([
                'status' => $status,
                'review_notes' => $payload['review_notes'] ?? null,
                'agent_fee_amount' => $payload['agent_fee_amount'] ?? 0,
                'legal_fee_amount' => $payload['legal_fee_amount'] ?? 0,
                'reviewed_by' => $actor->getKey(),
                'decided_at' => in_array($status, [RentalApplicationStatus::Approved, RentalApplicationStatus::Rejected, RentalApplicationStatus::Converted], true) ? now() : null,
            ])->save();

            $application->refresh()->loadMissing(['property', 'unit']);

            $this->logActivity->execute(
                user: $actor,
                action: 'rental_application_updated',
                description: "Rental application #{$application->getKey()} updated to {$application->status->label()}.",
                subject: $application,
                metadata: [
                    'status' => $application->status->value,
                    'agent_fee_amount' => $application->agent_fee_amount,
                    'legal_fee_amount' => $application->legal_fee_amount,
                ],
            );

            if ($application->applicant) {
                $application->applicant->notify(new RentalApplicationStatusUpdatedNotification($application));
            }

            return $application;
        });
    }
}
