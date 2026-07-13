<?php

namespace App\Livewire\Billing;

use App\Actions\Billing\CreateInvoiceAction;
use App\Actions\Billing\ReviewPaymentAction;
use App\Actions\Billing\SubmitPaymentAction;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TenancyStatus;
use App\Livewire\Concerns\InteractsWithFluxToast;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenancy;
use App\Support\Billing\BillingOptions;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use AuthorizesRequests, InteractsWithFluxToast, WithFileUploads;

    public array $invoiceTypeSelections = [];
    public array $invoiceIssueDates = [];
    public array $invoiceDueDates = [];
    public array $invoiceDescriptions = [];
    public array $invoiceAmounts = [];
    public array $invoiceDiscounts = [];
    public array $invoiceNotes = [];

    public array $paymentAmounts = [];
    public array $paymentMethodSelections = [];
    public array $paymentDates = [];
    public array $paymentNotes = [];
    public array $paymentProofs = [];

    public array $reviewStatuses = [];
    public array $reviewNotes = [];
    public array $rejectionReasons = [];

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $focus = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Invoice::class);
    }

    public function createInvoice(int $tenancyId, CreateInvoiceAction $createInvoiceAction): void
    {
        $tenancy = Tenancy::query()
            ->with(['property', 'unit', 'tenantUser'])
            ->visibleTo(auth()->user())
            ->findOrFail($tenancyId);

        $this->authorize('create', Invoice::class);

        $payload = validator($this->invoicePayload($tenancyId), $this->invoiceRules($tenancyId))->validate();
        $invoice = $createInvoiceAction->execute(auth()->user(), $tenancy, $payload);

        unset(
            $this->invoiceTypeSelections[$tenancyId],
            $this->invoiceIssueDates[$tenancyId],
            $this->invoiceDueDates[$tenancyId],
            $this->invoiceDescriptions[$tenancyId],
            $this->invoiceAmounts[$tenancyId],
            $this->invoiceDiscounts[$tenancyId],
            $this->invoiceNotes[$tenancyId],
        );

        $this->toast("Invoice {$invoice->invoice_number} issued successfully.", 'Invoice Issued');
    }

    public function submitPayment(int $invoiceId, SubmitPaymentAction $submitPaymentAction): void
    {
        $invoice = Invoice::query()
            ->with(['tenancy.property', 'tenantUser'])
            ->visibleTo(auth()->user())
            ->findOrFail($invoiceId);

        $this->authorize('create', Payment::class);
        $payload = validator($this->paymentPayload($invoiceId), $this->paymentRules())->validate();
        $proof = $payload['proof'] ?? null;
        unset($payload['proof']);

        $submitPaymentAction->execute(auth()->user(), $invoice, $payload, $proof);

        unset(
            $this->paymentAmounts[$invoiceId],
            $this->paymentMethodSelections[$invoiceId],
            $this->paymentDates[$invoiceId],
            $this->paymentNotes[$invoiceId],
            $this->paymentProofs[$invoiceId],
        );

        $this->toast('Payment submitted for verification.', 'Payment Submitted');
    }

    public function reviewPayment(int $paymentId, ReviewPaymentAction $reviewPaymentAction): void
    {
        $payment = Payment::query()
            ->with(['invoice', 'tenancy.property', 'tenantUser', 'receipt'])
            ->visibleTo(auth()->user())
            ->findOrFail($paymentId);

        $this->authorize('review', $payment);
        $payload = validator($this->reviewPayload($paymentId), $this->reviewRules($paymentId))->validate();
        $updated = $reviewPaymentAction->execute(auth()->user(), $payment, $payload);

        $this->reviewStatuses[$paymentId] = $updated->status->value;
        $this->reviewNotes[$paymentId] = $updated->review_notes ?? '';
        $this->rejectionReasons[$paymentId] = $updated->rejection_reason ?? '';

        $this->toast(
            $updated->status === PaymentStatus::Verified ? 'Payment verified and receipt issued.' : 'Payment review recorded.',
            $updated->status === PaymentStatus::Verified ? 'Payment Verified' : 'Payment Updated'
        );
    }

    public function canCreateInvoice(int $tenancyId): bool
    {
        $type = $this->invoiceTypeSelections[$tenancyId] ?? null;

        if (! filled($type) || ! filled($this->invoiceIssueDates[$tenancyId] ?? null) || ! filled($this->invoiceDueDates[$tenancyId] ?? null)) {
            return false;
        }

        return ! in_array($type, [InvoiceType::Miscellaneous->value, InvoiceType::CautionFee->value, InvoiceType::InspectionFee->value], true)
            || filled($this->invoiceAmounts[$tenancyId] ?? null);
    }

    public function canSubmitPayment(int $invoiceId): bool
    {
        return filled($this->paymentAmounts[$invoiceId] ?? null)
            && filled($this->paymentMethodSelections[$invoiceId] ?? null);
    }

    public function render()
    {
        $user = auth()->user();

        $billableTenancies = collect();

        if ($user->hasRole('admin') || $user->hasRole('landlord')) {
            $billableTenancies = Tenancy::query()
                ->with(['property', 'unit', 'tenantUser'])
                ->visibleTo($user)
                ->whereIn('status', [
                    TenancyStatus::PendingActivation,
                    TenancyStatus::Active,
                    TenancyStatus::RenewalPending,
                    TenancyStatus::Ending,
                ])
                ->when(trim($this->search) !== '', function ($query) {
                    $search = '%'.trim($this->search).'%';

                    $query->where(function ($inner) use ($search) {
                        $inner
                            ->where('tenant_name', 'like', $search)
                            ->orWhere('tenant_email', 'like', $search)
                            ->orWhereHas('property', fn ($propertyQuery) => $propertyQuery->where('title', 'like', $search))
                            ->orWhereHas('unit', fn ($unitQuery) => $unitQuery->where('unit_name', 'like', $search));
                    });
                })
                ->latest('lease_start_date')
                ->get();

            foreach ($billableTenancies as $tenancy) {
                $this->invoiceTypeSelections[$tenancy->id] ??= InvoiceType::Rent->value;
                $this->invoiceIssueDates[$tenancy->id] ??= now()->toDateString();
                $this->invoiceDueDates[$tenancy->id] ??= now()->addDays(7)->toDateString();
                $this->invoiceDescriptions[$tenancy->id] ??= '';
                $this->invoiceAmounts[$tenancy->id] ??= '';
                $this->invoiceDiscounts[$tenancy->id] ??= '0';
                $this->invoiceNotes[$tenancy->id] ??= '';
            }
        }

        $invoices = Invoice::query()
            ->with(['tenancy.property', 'tenancy.unit', 'items', 'activityLogs.user', 'payments.receipt', 'payments.activityLogs.user'])
            ->visibleTo($user)
            ->when($this->focus === 'overdue', fn ($query) => $query->where('status', InvoiceStatus::Overdue))
            ->when($this->focus === 'due_soon', fn ($query) => $query
                ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])
                ->whereDate('due_date', '<=', today()->addDays(7)))
            ->when(trim($this->search) !== '', function ($query) {
                $search = '%'.trim($this->search).'%';

                $query->where(function ($inner) use ($search) {
                    $inner
                        ->where('invoice_number', 'like', $search)
                        ->orWhere('notes', 'like', $search)
                        ->orWhereHas('tenantUser', fn ($tenantQuery) => $tenantQuery->where('name', 'like', $search)->orWhere('email', 'like', $search))
                        ->orWhereHas('tenancy.property', fn ($propertyQuery) => $propertyQuery->where('title', 'like', $search))
                        ->orWhereHas('tenancy.unit', fn ($unitQuery) => $unitQuery->where('unit_name', 'like', $search));
                });
            })
            ->latest('issue_date')
            ->limit(12)
            ->get();

        foreach ($invoices as $invoice) {
            $this->paymentAmounts[$invoice->id] ??= (string) $invoice->balance_amount;
            $this->paymentMethodSelections[$invoice->id] ??= PaymentMethod::BankTransfer->value;
            $this->paymentDates[$invoice->id] ??= now()->format('Y-m-d\TH:i');
            $this->paymentNotes[$invoice->id] ??= '';
        }

        $payments = Payment::query()
            ->with(['invoice', 'tenancy.property', 'receipt', 'activityLogs.user'])
            ->visibleTo($user)
            ->when($this->focus === 'pending_payments', fn ($query) => $query->where('status', PaymentStatus::PendingVerification))
            ->when(trim($this->search) !== '', function ($query) {
                $search = '%'.trim($this->search).'%';

                $query->where(function ($inner) use ($search) {
                    $inner
                        ->where('payment_reference', 'like', $search)
                        ->orWhere('notes', 'like', $search)
                        ->orWhereHas('tenantUser', fn ($tenantQuery) => $tenantQuery->where('name', 'like', $search)->orWhere('email', 'like', $search))
                        ->orWhereHas('invoice', fn ($invoiceQuery) => $invoiceQuery->where('invoice_number', 'like', $search))
                        ->orWhereHas('tenancy.property', fn ($propertyQuery) => $propertyQuery->where('title', 'like', $search));
                });
            })
            ->latest()
            ->limit(12)
            ->get();

        foreach ($payments as $payment) {
            $this->reviewStatuses[$payment->id] ??= $payment->status->value;
            $this->reviewNotes[$payment->id] ??= $payment->review_notes ?? '';
            $this->rejectionReasons[$payment->id] ??= $payment->rejection_reason ?? '';
        }

        return view('livewire.billing.index', [
            'billableTenancies' => $billableTenancies,
            'invoices' => $invoices,
            'payments' => $payments,
            ...BillingOptions::forForms(),
        ])->layout('components.layouts.app');
    }

    protected function invoicePayload(int $tenancyId): array
    {
        return [
            'invoice_type' => $this->invoiceTypeSelections[$tenancyId] ?? InvoiceType::Rent->value,
            'issue_date' => $this->invoiceIssueDates[$tenancyId] ?? now()->toDateString(),
            'due_date' => $this->invoiceDueDates[$tenancyId] ?? now()->addDays(7)->toDateString(),
            'description' => $this->blankToNull($this->invoiceDescriptions[$tenancyId] ?? null),
            'amount' => $this->blankToNull($this->invoiceAmounts[$tenancyId] ?? null),
            'discount_amount' => $this->blankToNull($this->invoiceDiscounts[$tenancyId] ?? null) ?? '0',
            'notes' => $this->blankToNull($this->invoiceNotes[$tenancyId] ?? null),
        ];
    }

    protected function invoiceRules(int $tenancyId): array
    {
        $type = $this->invoiceTypeSelections[$tenancyId] ?? InvoiceType::Rent->value;
        $customAmountRequired = in_array($type, [InvoiceType::Miscellaneous->value, InvoiceType::CautionFee->value, InvoiceType::InspectionFee->value], true);

        return [
            'invoice_type' => ['required', Rule::in(array_map(static fn (InvoiceType $type): string => $type->value, InvoiceType::cases()))],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => [$customAmountRequired ? 'required' : 'nullable', 'numeric', 'min:0.01'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1500'],
        ];
    }

    protected function paymentPayload(int $invoiceId): array
    {
        return [
            'amount' => $this->paymentAmounts[$invoiceId] ?? null,
            'payment_method' => $this->paymentMethodSelections[$invoiceId] ?? PaymentMethod::BankTransfer->value,
            'paid_at' => $this->paymentDates[$invoiceId] ?? now()->format('Y-m-d\TH:i'),
            'notes' => $this->blankToNull($this->paymentNotes[$invoiceId] ?? null),
            'proof' => $this->paymentProofs[$invoiceId] ?? null,
        ];
    }

    protected function paymentRules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(array_map(static fn (PaymentMethod $method): string => $method->value, PaymentMethod::cases()))],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1500'],
            'proof' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    protected function reviewPayload(int $paymentId): array
    {
        return [
            'status' => $this->reviewStatuses[$paymentId] ?? PaymentStatus::PendingVerification->value,
            'review_notes' => $this->blankToNull($this->reviewNotes[$paymentId] ?? null),
            'rejection_reason' => $this->blankToNull($this->rejectionReasons[$paymentId] ?? null),
        ];
    }

    protected function reviewRules(int $paymentId): array
    {
        $status = $this->reviewStatuses[$paymentId] ?? PaymentStatus::PendingVerification->value;

        return [
            'status' => ['required', Rule::in([PaymentStatus::Verified->value, PaymentStatus::Rejected->value])],
            'review_notes' => ['nullable', 'string', 'max:1500'],
            'rejection_reason' => [$status === PaymentStatus::Rejected->value ? 'required' : 'nullable', 'string', 'max:1500'],
        ];
    }

    protected function blankToNull(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }
}
