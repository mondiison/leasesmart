<?php

namespace App\Actions\Inspections;

use App\Actions\Activity\LogActivityAction;
use App\Enums\InspectionStatus;
use App\Models\Inspection;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Notifications\InspectionRequestReceivedNotification;
use App\Notifications\InspectionRequestedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CreateInspectionRequestAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(Property $property, array $payload): Inspection
    {
        return DB::transaction(function () use ($property, $payload): Inspection {
            /** @var PropertyUnit|null $unit */
            $unit = $property->publicUnits()->findOrFail($payload['property_unit_id']);

            $inspection = Inspection::query()->create([
                'property_id' => $property->getKey(),
                'property_unit_id' => $unit->getKey(),
                'requester_user_id' => auth()->id(),
                'status' => InspectionStatus::Requested,
                'source' => 'marketplace',
                'requester_name' => $payload['requester_name'],
                'requester_email' => $payload['requester_email'],
                'requester_phone' => $payload['requester_phone'],
                'requested_for_date' => $payload['requested_for_date'] ?? null,
                'requested_for_time' => $payload['requested_for_time'] ?? null,
                'message' => $payload['message'] ?? null,
            ]);

            $this->logActivity->execute(
                user: auth()->user(),
                action: 'inspection_requested',
                description: "Inspection requested for {$property->title}".($unit->unit_name ? " ({$unit->unit_name})" : '.'),
                subject: $inspection,
                metadata: [
                    'property_id' => $property->getKey(),
                    'property_unit_id' => $unit->getKey(),
                    'source' => 'marketplace',
                ],
            );

            Notification::send(
                $this->recipientsFor($property),
                new InspectionRequestedNotification($inspection),
            );

            Notification::route('mail', $inspection->requester_email)
                ->notify(new InspectionRequestReceivedNotification($inspection));

            return $inspection;
        });
    }

    /**
     * @return Collection<int, mixed>
     */
    protected function recipientsFor(Property $property): Collection
    {
        return collect([
            $property->landlord?->user,
            $property->caretaker?->user,
            ...\App\Models\User::role('admin')->get(),
        ])->filter(fn ($user) => $user !== null)->unique('id')->values();
    }
}
