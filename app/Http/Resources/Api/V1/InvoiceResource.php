<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoice_type' => $this->invoice_type?->value,
            'invoice_type_label' => $this->invoice_type?->label(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'issue_date' => $this->issue_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'subtotal_amount' => (float) $this->subtotal_amount,
            'discount_amount' => (float) $this->discount_amount,
            'total_amount' => (float) $this->total_amount,
            'balance_amount' => (float) $this->balance_amount,
            'notes' => $this->notes,
            'tenancy' => $this->whenLoaded('tenancy', fn (): array => [
                'id' => $this->tenancy->id,
                'status' => $this->tenancy->status?->value,
                'tenant_name' => $this->tenancy->tenant_name,
            ]),
            'property' => $this->whenLoaded('tenancy.property', fn (): array => [
                'id' => $this->tenancy->property->id,
                'title' => $this->tenancy->property->title,
                'slug' => $this->tenancy->property->slug,
            ]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item): array => [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_amount' => (float) $item->unit_amount,
                'total_amount' => (float) $item->total_amount,
            ])->all()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
