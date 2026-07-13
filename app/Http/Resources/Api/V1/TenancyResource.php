<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenancyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'tenant_name' => $this->tenant_name,
            'tenant_email' => $this->tenant_email,
            'tenant_phone' => $this->tenant_phone,
            'lease_start_date' => $this->lease_start_date?->toDateString(),
            'lease_end_date' => $this->lease_end_date?->toDateString(),
            'move_in_date' => $this->move_in_date?->toDateString(),
            'rent_amount' => (float) $this->rent_amount,
            'service_charge_amount' => (float) $this->service_charge_amount,
            'billing_cycle' => $this->billing_cycle?->value,
            'property' => $this->whenLoaded('property', fn (): array => [
                'id' => $this->property->id,
                'title' => $this->property->title,
                'slug' => $this->property->slug,
                'city' => $this->property->city,
                'state' => $this->property->state,
            ]),
            'unit' => $this->whenLoaded('unit', fn (): array => [
                'id' => $this->unit->id,
                'unit_name' => $this->unit->unit_name,
                'unit_code' => $this->unit->unit_code,
                'occupancy_status' => $this->unit->occupancy_status?->value,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
