<?php

namespace App\Actions\Maintenance;

use App\Actions\Activity\LogActivityAction;
use App\Enums\MaintenanceStatus;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\User;
use App\Notifications\MaintenanceRequestCreatedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CreateMaintenanceRequestAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    public function execute(User $actor, array $payload, array $attachments = []): MaintenanceRequest
    {
        return DB::transaction(function () use ($actor, $payload, $attachments): MaintenanceRequest {
            $property = Property::query()->findOrFail($payload['property_id']);
            $tenancy = ! empty($payload['tenancy_id']) ? Tenancy::query()->findOrFail($payload['tenancy_id']) : null;

            $request = MaintenanceRequest::query()->create([
                'property_id' => $property->getKey(),
                'property_unit_id' => $payload['property_unit_id'] ?? null,
                'tenancy_id' => $tenancy?->getKey(),
                'tenant_user_id' => $payload['tenant_user_id'] ?? ($actor->hasRole('tenant') ? $actor->getKey() : null),
                'title' => $payload['title'],
                'description' => $payload['description'],
                'category' => $payload['category'] ?? null,
                'priority' => $payload['priority'],
                'status' => MaintenanceStatus::Open,
                'reported_at' => now(),
                'assigned_to' => $payload['assigned_to'] ?? null,
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);

            foreach ($attachments as $attachment) {
                if ($attachment instanceof UploadedFile) {
                    $request->addMedia($attachment)->toMediaCollection('attachments');
                }
            }

            $request->updates()->create([
                'user_id' => $actor->getKey(),
                'status' => MaintenanceStatus::Open,
                'message' => 'Maintenance request opened.',
                'metadata' => [
                    'priority' => $request->priority->value,
                    'assigned_to' => $request->assigned_to,
                ],
            ]);

            $this->logActivity->execute(
                user: $actor,
                action: 'maintenance_request_created',
                description: "Created maintenance request {$request->title} for {$property->title}.",
                subject: $request,
                metadata: [
                    'property_id' => $property->getKey(),
                    'priority' => $request->priority->value,
                    'status' => $request->status->value,
                ],
            );

            Notification::send($this->recipientsFor($request), new MaintenanceRequestCreatedNotification($request->load(['property', 'tenantUser'])));

            return $request;
        });
    }

    protected function recipientsFor(MaintenanceRequest $request)
    {
        return collect([
            $request->property->landlord?->user,
            $request->property->caretaker?->user,
            $request->assignee,
            ...User::role('admin')->get(),
        ])->filter()->unique('id')->values();
    }
}
