<?php

namespace App\Actions\Properties;

use App\Actions\Activity\LogActivityAction;
use App\Models\PropertyUnit;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdatePropertyUnitAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(User $actor, PropertyUnit $unit, array $payload): PropertyUnit
    {
        return DB::transaction(function () use ($actor, $unit, $payload): PropertyUnit {
            $unit->update([
                'unit_code' => $payload['unit_code'] ?? null,
                'unit_name' => $payload['unit_name'],
                'unit_type' => $payload['unit_type'] ?? null,
                'floor_label' => $payload['floor_label'] ?? null,
                'bedrooms' => $payload['bedrooms'] ?? null,
                'bathrooms' => $payload['bathrooms'] ?? null,
                'toilets' => $payload['toilets'] ?? null,
                'size_sqm' => $payload['size_sqm'] ?? null,
                'occupancy_status' => $payload['occupancy_status'],
                'rent_amount' => $payload['rent_amount'],
                'billing_cycle' => $payload['billing_cycle'],
                'service_charge_amount' => $payload['service_charge_amount'] ?? 0,
                'caution_fee_amount' => $payload['caution_fee_amount'] ?? 0,
                'inspection_fee_amount' => $payload['inspection_fee_amount'] ?? 0,
                'available_from' => $payload['available_from'] ?? null,
                'description' => $payload['description'] ?? null,
                'is_listed' => (bool) ($payload['is_listed'] ?? false),
            ]);

            $unit->amenities()->sync($payload['amenity_ids'] ?? []);
            $this->attachMedia($unit, $payload['media'] ?? []);

            $this->logActivity->execute(
                user: $actor,
                action: 'property_unit_updated',
                description: "Updated unit {$unit->unit_name} for {$unit->property->title}.",
                subject: $unit,
                metadata: ['property_id' => $unit->property_id],
            );

            return $unit->fresh(['amenities', 'media', 'property']);
        });
    }

    /**
     * @param array<int, UploadedFile> $media
     */
    protected function attachMedia(PropertyUnit $unit, array $media): void
    {
        foreach ($media as $file) {
            $unit->addMedia($file)->toMediaCollection('gallery');
        }
    }
}
