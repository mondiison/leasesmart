<?php

namespace App\Actions\Inspections;

use App\Actions\Activity\LogActivityAction;
use App\Models\Inspection;
use App\Models\User;
use App\Notifications\InspectionStatusUpdatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class UpdateInspectionAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(User $actor, Inspection $inspection, array $payload): Inspection
    {
        return DB::transaction(function () use ($actor, $inspection, $payload): Inspection {
            $inspection->fill([
                'status' => $payload['status'],
                'scheduled_at' => $payload['scheduled_at'] ?? null,
                'internal_notes' => $payload['internal_notes'] ?? null,
                'handled_by' => $actor->getKey(),
            ])->save();

            $inspection->refresh()->loadMissing(['property', 'unit']);

            $this->logActivity->execute(
                user: $actor,
                action: 'inspection_updated',
                description: "Inspection #{$inspection->getKey()} updated to {$inspection->status->label()}.",
                subject: $inspection,
                metadata: [
                    'status' => $inspection->status->value,
                    'scheduled_at' => optional($inspection->scheduled_at)?->toIso8601String(),
                ],
            );

            Notification::route('mail', $inspection->requester_email)
                ->notify(new InspectionStatusUpdatedNotification($inspection));

            if ($inspection->requester) {
                $inspection->requester->notify(new InspectionStatusUpdatedNotification($inspection));
            }

            return $inspection;
        });
    }
}
