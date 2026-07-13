<?php

namespace App\Actions\Properties;

use App\Actions\Activity\LogActivityAction;
use App\Enums\PropertyPublishStatus;
use App\Models\Property;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class UpdatePropertyPublishStatusAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    public function execute(User $actor, Property $property, PropertyPublishStatus $status): Property
    {
        return DB::transaction(function () use ($actor, $property, $status): Property {
            if ($status === PropertyPublishStatus::Published) {
                $this->ensurePublishable($property);
            }

            $property->update([
                'publish_status' => $status,
                'published_at' => $status === PropertyPublishStatus::Published ? ($property->published_at ?? now()) : null,
                'updated_by' => $actor->getKey(),
            ]);

            $this->logActivity->execute(
                user: $actor,
                action: 'property_publish_status_updated',
                description: "Updated {$property->title} to {$status->label()}.",
                subject: $property,
                metadata: ['publish_status' => $status->value],
            );

            return $property->fresh(['units']);
        });
    }

    protected function ensurePublishable(Property $property): void
    {
        if ($property->landlord_id === null) {
            throw new DomainException('A landlord must be assigned before publishing this property.');
        }

        if (! $property->units()->where('is_listed', true)->exists()) {
            throw new DomainException('Create at least one listed unit before publishing this property.');
        }
    }
}
