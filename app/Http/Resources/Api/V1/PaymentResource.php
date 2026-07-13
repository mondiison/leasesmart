<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_reference' => $this->payment_reference,
            'payment_method' => $this->payment_method?->value,
            'payment_method_label' => $this->payment_method?->label(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'amount' => (float) $this->amount,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'verified_at' => $this->verified_at?->toIso8601String(),
            'invoice' => $this->whenLoaded('invoice', fn (): ?array => $this->invoice ? [
                'id' => $this->invoice->id,
                'invoice_number' => $this->invoice->invoice_number,
                'status' => $this->invoice->status?->value,
                'balance_amount' => (float) $this->invoice->balance_amount,
            ] : null),
            'tenancy' => $this->whenLoaded('tenancy', fn (): ?array => $this->tenancy ? [
                'id' => $this->tenancy->id,
                'status' => $this->tenancy->status?->value,
                'tenant_name' => $this->tenancy->tenant_name,
            ] : null),
            'receipt' => $this->whenLoaded('receipt', fn (): ?array => $this->receipt ? [
                'id' => $this->receipt->id,
                'receipt_number' => $this->receipt->receipt_number,
                'issued_at' => $this->receipt->issued_at?->toIso8601String(),
                'amount' => (float) $this->receipt->amount,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
