<?php

namespace App\Actions\Billing;

use App\Actions\Activity\LogActivityAction;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentSubmittedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class SubmitPaymentAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    public function execute(User $actor, Invoice $invoice, array $payload, ?UploadedFile $proof = null): Payment
    {
        return DB::transaction(function () use ($actor, $invoice, $payload, $proof): Payment {
            $payment = Payment::query()->create([
                'tenancy_id' => $invoice->tenancy_id,
                'tenant_user_id' => $invoice->tenant_user_id,
                'invoice_id' => $invoice->getKey(),
                'submitted_by' => $actor->getKey(),
                'payment_reference' => $this->nextPaymentReference(),
                'payment_method' => $payload['payment_method'],
                'amount' => $payload['amount'],
                'paid_at' => $payload['paid_at'] ?? now(),
                'status' => PaymentStatus::PendingVerification,
                'external_transaction_id' => $payload['external_transaction_id'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ]);

            if ($proof !== null) {
                $payment->addMedia($proof)->toMediaCollection('proofs');
            }

            $this->logActivity->execute(
                user: $actor,
                action: 'payment_submitted',
                description: "Submitted payment {$payment->payment_reference} for invoice {$invoice->invoice_number}.",
                subject: $payment,
                metadata: [
                    'invoice_id' => $invoice->getKey(),
                    'tenancy_id' => $invoice->tenancy_id,
                    'amount' => (float) $payment->amount,
                ],
            );

            Notification::send($this->reviewRecipients($invoice), new PaymentSubmittedNotification($payment->load('invoice.tenancy.property')));

            return $payment;
        });
    }

    protected function reviewRecipients(Invoice $invoice)
    {
        return collect([
            $invoice->tenancy->property->landlord?->user,
            ...User::role('admin')->get(),
        ])->filter()->unique('id')->values();
    }

    protected function nextPaymentReference(): string
    {
        do {
            $reference = 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Payment::query()->where('payment_reference', $reference)->exists());

        return $reference;
    }
}
