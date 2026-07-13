<?php

namespace App\Actions\Billing;

use App\Actions\Activity\LogActivityAction;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use App\Notifications\PaymentReviewedNotification;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReviewPaymentAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    public function execute(User $actor, Payment $payment, array $payload): Payment
    {
        return DB::transaction(function () use ($actor, $payment, $payload): Payment {
            $status = PaymentStatus::from($payload['status']);

            if ($payment->status === PaymentStatus::Verified && $status === PaymentStatus::Verified) {
                throw new DomainException('This payment has already been verified.');
            }

            $payment->forceFill([
                'status' => $status,
                'review_notes' => $payload['review_notes'] ?? null,
                'rejection_reason' => $status === PaymentStatus::Rejected ? ($payload['rejection_reason'] ?? null) : null,
                'verified_by' => $status === PaymentStatus::Verified ? $actor->getKey() : null,
                'verified_at' => $status === PaymentStatus::Verified ? now() : null,
            ])->save();

            if ($status === PaymentStatus::Verified) {
                $this->applyAllocations($payment);
                $this->createReceipt($actor, $payment);
            }

            $this->logActivity->execute(
                user: $actor,
                action: 'payment_reviewed',
                description: "Reviewed payment {$payment->payment_reference} as {$status->label()}.",
                subject: $payment,
                metadata: [
                    'status' => $status->value,
                    'invoice_id' => $payment->invoice_id,
                    'tenancy_id' => $payment->tenancy_id,
                ],
            );

            if ($payment->tenantUser !== null) {
                $payment->tenantUser->notify(new PaymentReviewedNotification($payment->load('invoice.tenancy.property', 'receipt')));
            }

            return $payment->fresh(['allocations.invoice', 'receipt']);
        });
    }

    protected function applyAllocations(Payment $payment): void
    {
        $payment->allocations()->delete();

        $remaining = (float) $payment->amount;
        $candidates = collect();

        if ($payment->invoice !== null) {
            $candidates->push($payment->invoice->fresh());
        }

        $otherInvoices = Invoice::query()
            ->where('tenancy_id', $payment->tenancy_id)
            ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue])
            ->when($payment->invoice_id !== null, fn ($query) => $query->whereKeyNot($payment->invoice_id))
            ->orderBy('due_date')
            ->get();

        $candidates = $candidates->merge($otherInvoices);

        foreach ($candidates as $invoice) {
            $invoice = $invoice->fresh();
            $balance = (float) $invoice->balance_amount;

            if ($balance <= 0 || $remaining <= 0) {
                continue;
            }

            $allocationAmount = min($balance, $remaining);

            $payment->allocations()->create([
                'invoice_id' => $invoice->getKey(),
                'amount' => $allocationAmount,
            ]);

            $invoice->forceFill([
                'balance_amount' => max($balance - $allocationAmount, 0),
            ])->save();

            $invoice->refreshStatusFromBalance();
            $remaining -= $allocationAmount;
        }
    }

    protected function createReceipt(User $actor, Payment $payment): Receipt
    {
        $existingReceipt = $payment->receipt()->first();

        if ($existingReceipt !== null) {
            return $existingReceipt;
        }

        return Receipt::query()->create([
            'payment_id' => $payment->getKey(),
            'tenancy_id' => $payment->tenancy_id,
            'tenant_user_id' => $payment->tenant_user_id,
            'receipt_number' => $this->nextReceiptNumber(),
            'amount' => $payment->amount,
            'issued_at' => now(),
            'notes' => $payment->review_notes,
            'issued_by' => $actor->getKey(),
        ]);
    }

    protected function nextReceiptNumber(): string
    {
        do {
            $number = 'RCT-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Receipt::query()->where('receipt_number', $number)->exists());

        return $number;
    }
}
