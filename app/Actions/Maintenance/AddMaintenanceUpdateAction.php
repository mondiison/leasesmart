<?php

namespace App\Actions\Maintenance;

use App\Actions\Activity\LogActivityAction;
use App\Enums\MaintenanceStatus;
use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Notifications\MaintenanceRequestUpdatedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class AddMaintenanceUpdateAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    public function execute(User $actor, MaintenanceRequest $request, array $payload, array $attachments = []): MaintenanceRequest
    {
        return DB::transaction(function () use ($actor, $request, $payload, $attachments): MaintenanceRequest {
            $status = MaintenanceStatus::from($payload['status']);

            $request->forceFill([
                'status' => $status,
                'assigned_to' => $payload['assigned_to'] ?? $request->assigned_to,
                'updated_by' => $actor->getKey(),
                'resolved_at' => $status === MaintenanceStatus::Resolved ? now() : ($status === MaintenanceStatus::Closed ? $request->resolved_at : null),
                'closed_at' => $status === MaintenanceStatus::Closed ? now() : null,
            ])->save();

            $update = $request->updates()->create([
                'user_id' => $actor->getKey(),
                'status' => $status,
                'message' => $payload['message'] ?? null,
                'metadata' => [
                    'assigned_to' => $request->assigned_to,
                ],
            ]);

            foreach ($attachments as $attachment) {
                if ($attachment instanceof UploadedFile) {
                    $update->addMedia($attachment)->toMediaCollection('attachments');
                }
            }

            $this->logActivity->execute(
                user: $actor,
                action: 'maintenance_request_updated',
                description: "Updated maintenance request {$request->title} to {$status->label()}.",
                subject: $request,
                metadata: [
                    'status' => $status->value,
                    'assigned_to' => $request->assigned_to,
                ],
            );

            collect([$request->tenantUser, $request->property->landlord?->user, $request->property->caretaker?->user, $request->assignee])
                ->filter()
                ->unique('id')
                ->each(fn (User $recipient) => $recipient->notify(new MaintenanceRequestUpdatedNotification($request->fresh(['property', 'tenantUser']))));

            return $request->fresh(['updates.user', 'assignee', 'property', 'unit', 'tenantUser']);
        });
    }
}
