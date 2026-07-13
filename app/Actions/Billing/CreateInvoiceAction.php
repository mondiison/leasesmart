<?php

namespace App\Actions\Billing;

use App\Actions\Activity\LogActivityAction;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Invoice;
use App\Models\Tenancy;
use App\Models\User;
use App\Notifications\InvoiceIssuedNotification;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateInvoiceAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    public function execute(User $actor, Tenancy $tenancy, array $payload): Invoice
    {
        if (! in_array($tenancy->status->value, ['pending_activation', 'active', 'renewal_pending', 'ending'], true)) {
            throw new DomainException('Invoices can only be issued for active or upcoming tenancy records.');
        }

        return DB::transaction(function () use ($actor, $tenancy, $payload): Invoice {
            $type = InvoiceType::from($payload['invoice_type']);
            $subtotal = $this->resolveSubtotal($tenancy, $type, $payload);
            $discount = (float) ($payload['discount_amount'] ?? 0);
            $total = max($subtotal - $discount, 0);

            $invoice = Invoice::query()->create([
                'tenancy_id' => $tenancy->getKey(),
                'tenant_user_id' => $tenancy->tenant_user_id,
                'invoice_number' => $this->nextInvoiceNumber(),
                'invoice_type' => $type,
                'issue_date' => $payload['issue_date'],
                'due_date' => $payload['due_date'],
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'balance_amount' => $total,
                'status' => InvoiceStatus::Issued,
                'notes' => $payload['notes'] ?? null,
                'issued_by' => $actor->getKey(),
            ]);

            $invoice->items()->create([
                'item_type' => $type->value,
                'description' => $this->lineDescription($tenancy, $type, $payload),
                'quantity' => 1,
                'unit_amount' => $subtotal,
                'total_amount' => $subtotal,
                'metadata' => [
                    'tenancy_id' => $tenancy->getKey(),
                    'property_id' => $tenancy->property_id,
                    'property_unit_id' => $tenancy->property_unit_id,
                ],
            ]);

            $this->logActivity->execute(
                user: $actor,
                action: 'invoice_created',
                description: "Issued {$type->label()} invoice {$invoice->invoice_number} for {$tenancy->tenant_name}.",
                subject: $invoice,
                metadata: [
                    'tenancy_id' => $tenancy->getKey(),
                    'invoice_type' => $type->value,
                    'total_amount' => $total,
                ],
            );

            if ($tenancy->tenantUser !== null) {
                $tenancy->tenantUser->notify(new InvoiceIssuedNotification($invoice->load('tenancy.property')));
            }

            return $invoice;
        });
    }

    protected function resolveSubtotal(Tenancy $tenancy, InvoiceType $type, array $payload): float
    {
        return match ($type) {
            InvoiceType::Rent => (float) $tenancy->rent_amount,
            InvoiceType::ServiceCharge => (float) $tenancy->service_charge_amount,
            InvoiceType::CautionFee, InvoiceType::InspectionFee, InvoiceType::Miscellaneous => (function () use ($payload): float {
                $amount = $payload['amount'] ?? null;

                if ($amount === null || (float) $amount <= 0) {
                    throw new DomainException('A custom amount is required for this invoice type.');
                }

                return (float) $amount;
            })(),
        };
    }

    protected function lineDescription(Tenancy $tenancy, InvoiceType $type, array $payload): string
    {
        return $payload['description']
            ?? match ($type) {
                InvoiceType::Rent => "Rent charge for {$tenancy->property->title}",
                InvoiceType::ServiceCharge => "Service charge for {$tenancy->property->title}",
                InvoiceType::CautionFee => "Caution fee for {$tenancy->property->title}",
                InvoiceType::InspectionFee => "Inspection fee for {$tenancy->property->title}",
                InvoiceType::Miscellaneous => 'Miscellaneous billing item',
            };
    }

    protected function nextInvoiceNumber(): string
    {
        do {
            $number = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Invoice::query()->where('invoice_number', $number)->exists());

        return $number;
    }
}
