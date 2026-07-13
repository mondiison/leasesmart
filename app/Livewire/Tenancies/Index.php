<?php

namespace App\Livewire\Tenancies;

use App\Actions\Tenancies\CreateTenancyAction;
use App\Actions\Tenancies\UpdateTenancyAction;
use App\Actions\Tenancies\UploadTenancyDocumentsAction;
use App\Actions\Activity\LogActivityAction;
use App\Enums\BillingCycle;
use App\Enums\InvoiceStatus;
use App\Enums\MaintenanceStatus;
use App\Enums\PaymentStatus;
use App\Enums\TenancyStatus;
use App\Livewire\Concerns\InteractsWithFluxToast;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\RentalApplication;
use App\Models\Tenancy;
use App\Support\Tenancies\TenancyOptions;
use DomainException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Index extends Component
{
    use AuthorizesRequests, InteractsWithFluxToast, WithFileUploads, WithPagination;

    #[Url]
    public string $status = '';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $focus = '';

    /** @var array<int, string> */
    public array $createStatus = [];
    /** @var array<int, string> */
    public array $createLeaseStartDates = [];
    /** @var array<int, string> */
    public array $createLeaseEndDates = [];
    /** @var array<int, string> */
    public array $createMoveInDates = [];
    /** @var array<int, string> */
    public array $createRentAmounts = [];
    /** @var array<int, string> */
    public array $createServiceCharges = [];
    /** @var array<int, string> */
    public array $createBillingCycles = [];
    /** @var array<int, string> */
    public array $createNotes = [];

    /** @var array<int, string> */
    public array $updateStatus = [];
    /** @var array<int, string> */
    public array $updateLeaseStartDates = [];
    /** @var array<int, string> */
    public array $updateLeaseEndDates = [];
    /** @var array<int, string> */
    public array $updateMoveInDates = [];
    /** @var array<int, string> */
    public array $updateRentAmounts = [];
    /** @var array<int, string> */
    public array $updateServiceCharges = [];
    /** @var array<int, string> */
    public array $updateBillingCycles = [];
    /** @var array<int, string> */
    public array $updateNotes = [];

    /** @var array<int, array<int, mixed>> */
    public array $documents = [];

    /** @var array<int, string> */
    public array $documentLabels = [];

    public function mount(): void
    {
        $this->authorize('viewAny', Tenancy::class);
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFocus(): void
    {
        $this->resetPage();
    }

    public function createFromApplication(int $applicationId, CreateTenancyAction $createTenancyAction): void
    {
        $application = RentalApplication::query()->with(['property.landlord', 'unit', 'tenancy'])->findOrFail($applicationId);
        $this->authorize('create', Tenancy::class);

        try {
            $payload = $this->validateCreatePayload($applicationId, $application);

            $createTenancyAction->execute(auth()->user(), $application, $payload);

            unset(
                $this->createStatus[$applicationId],
                $this->createLeaseStartDates[$applicationId],
                $this->createLeaseEndDates[$applicationId],
                $this->createMoveInDates[$applicationId],
                $this->createRentAmounts[$applicationId],
                $this->createServiceCharges[$applicationId],
                $this->createBillingCycles[$applicationId],
                $this->createNotes[$applicationId],
            );

            $this->resetErrorBag("createTenancy.$applicationId");
            $this->toast('Tenancy created successfully.');
        } catch (DomainException $exception) {
            $this->addError("createTenancy.$applicationId", $exception->getMessage());
        }
    }

    public function saveTenancy(int $tenancyId, UpdateTenancyAction $updateTenancyAction): void
    {
        $tenancy = Tenancy::query()->with(['property.landlord', 'unit'])->findOrFail($tenancyId);
        $this->authorize('update', $tenancy);

        $payload = $this->validateUpdatePayload($tenancyId, $tenancy);
        $updated = $updateTenancyAction->execute(auth()->user(), $tenancy, $payload);

        $this->updateStatus[$tenancyId] = $updated->status->value;
        $this->updateLeaseStartDates[$tenancyId] = $updated->lease_start_date?->toDateString() ?? '';
        $this->updateLeaseEndDates[$tenancyId] = $updated->lease_end_date?->toDateString() ?? '';
        $this->updateMoveInDates[$tenancyId] = $updated->move_in_date?->toDateString() ?? '';
        $this->updateRentAmounts[$tenancyId] = (string) $updated->rent_amount;
        $this->updateServiceCharges[$tenancyId] = (string) $updated->service_charge_amount;
        $this->updateBillingCycles[$tenancyId] = $updated->billing_cycle->value;
        $this->updateNotes[$tenancyId] = $updated->notes ?? '';

        $this->toast('Tenancy updated successfully.');
    }

    public function uploadDocuments(int $tenancyId, UploadTenancyDocumentsAction $uploadTenancyDocumentsAction): void
    {
        $tenancy = Tenancy::query()->with(['property.landlord', 'tenantUser'])->findOrFail($tenancyId);
        $this->authorize('update', $tenancy);

        $this->validate([
            "documents.$tenancyId" => ['required', 'array', 'min:1'],
            "documents.$tenancyId.*" => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        $uploadTenancyDocumentsAction->execute(auth()->user(), $tenancy, $this->documents[$tenancyId] ?? []);

        unset($this->documents[$tenancyId]);
        $this->resetValidation("documents.$tenancyId");

        $this->toast('Tenancy document uploaded successfully.');
    }

    public function renameDocument(int $mediaId, LogActivityAction $logActivity): void
    {
        $media = Media::query()->with('model')->findOrFail($mediaId);
        abort_unless($media->model instanceof Tenancy && $media->collection_name === 'documents', 404);

        $this->authorize('update', $media->model);

        $payload = validator([
            'label' => $this->documentLabels[$mediaId] ?? $media->name,
        ], [
            'label' => ['required', 'string', 'max:120'],
        ])->validate();

        $oldName = $media->name;

        $media->forceFill(['name' => $payload['label']])->save();

        $logActivity->execute(
            user: auth()->user(),
            action: 'tenancy_document_renamed',
            description: "Renamed tenancy document from {$oldName} to {$media->name}.",
            subject: $media->model,
            metadata: ['media_id' => $media->getKey()],
        );

        $this->toast('Tenancy document label updated.');
    }

    public function deleteDocument(int $mediaId, LogActivityAction $logActivity): void
    {
        $media = Media::query()->with('model')->findOrFail($mediaId);
        abort_unless($media->model instanceof Tenancy && $media->collection_name === 'documents', 404);

        $this->authorize('update', $media->model);

        $tenancy = $media->model;
        $name = $media->name;

        $media->delete();

        $logActivity->execute(
            user: auth()->user(),
            action: 'tenancy_document_deleted',
            description: "Deleted tenancy document {$name}.",
            subject: $tenancy,
            metadata: ['media_id' => $mediaId],
        );

        unset($this->documentLabels[$mediaId]);

        $this->toast('Tenancy document deleted.');
    }

    public function canCreateTenancy(int $applicationId): bool
    {
        return filled($this->createStatus[$applicationId] ?? null)
            && filled($this->createLeaseStartDates[$applicationId] ?? null)
            && filled($this->createRentAmounts[$applicationId] ?? null)
            && filled($this->createBillingCycles[$applicationId] ?? null);
    }

    public function canUpdateTenancy(int $tenancyId): bool
    {
        return filled($this->updateStatus[$tenancyId] ?? null)
            && filled($this->updateLeaseStartDates[$tenancyId] ?? null)
            && filled($this->updateRentAmounts[$tenancyId] ?? null)
            && filled($this->updateBillingCycles[$tenancyId] ?? null);
    }

    public function render()
    {
        $user = auth()->user();
        $isTenant = $user->hasRole('tenant');

        $tenanciesQuery = Tenancy::query()
            ->with(['property', 'unit', 'tenantUser', 'media', 'activityLogs.user'])
            ->visibleTo($user)
            ->latest();

        if ($this->status !== '') {
            $tenanciesQuery->where('status', $this->status);
        }

        if ($this->focus === 'expiring') {
            $tenanciesQuery
                ->whereNotNull('lease_end_date')
                ->whereDate('lease_end_date', '>=', today())
                ->whereDate('lease_end_date', '<=', today()->addDays(60))
                ->orderBy('lease_end_date');
        }

        if (trim($this->search) !== '') {
            $search = '%'.trim($this->search).'%';

            $tenanciesQuery->where(function ($inner) use ($search) {
                $inner
                    ->where('tenant_name', 'like', $search)
                    ->orWhere('tenant_email', 'like', $search)
                    ->orWhere('tenant_phone', 'like', $search)
                    ->orWhereHas('property', fn ($propertyQuery) => $propertyQuery->where('title', 'like', $search))
                    ->orWhereHas('unit', fn ($unitQuery) => $unitQuery->where('unit_name', 'like', $search));
            });
        }

        $tenancies = $tenanciesQuery->paginate(10);

        foreach ($tenancies as $tenancy) {
            $this->updateStatus[$tenancy->id] ??= $tenancy->status->value;
            $this->updateLeaseStartDates[$tenancy->id] ??= $tenancy->lease_start_date?->toDateString() ?? '';
            $this->updateLeaseEndDates[$tenancy->id] ??= $tenancy->lease_end_date?->toDateString() ?? '';
            $this->updateMoveInDates[$tenancy->id] ??= $tenancy->move_in_date?->toDateString() ?? '';
            $this->updateRentAmounts[$tenancy->id] ??= (string) $tenancy->rent_amount;
            $this->updateServiceCharges[$tenancy->id] ??= (string) $tenancy->service_charge_amount;
            $this->updateBillingCycles[$tenancy->id] ??= $tenancy->billing_cycle->value;
            $this->updateNotes[$tenancy->id] ??= $tenancy->notes ?? '';

            foreach ($tenancy->getMedia('documents') as $document) {
                $this->documentLabels[$document->id] ??= $document->name;
            }
        }

        $convertibleApplications = collect();

        if ($user->hasRole('admin') || $user->hasRole('landlord')) {
            $convertibleApplications = RentalApplication::query()
                ->with(['property', 'unit', 'tenancy'])
                ->visibleTo($user)
                ->where('status', 'approved')
                ->whereDoesntHave('tenancy')
                ->latest('decided_at')
                ->get();

            foreach ($convertibleApplications as $application) {
                $this->createStatus[$application->id] ??= TenancyStatus::PendingActivation->value;
                $this->createLeaseStartDates[$application->id] ??= $application->preferred_move_in_date?->toDateString() ?? now()->toDateString();
                $this->createLeaseEndDates[$application->id] ??= '';
                $this->createMoveInDates[$application->id] ??= $application->preferred_move_in_date?->toDateString() ?? '';
                $this->createRentAmounts[$application->id] ??= (string) ($application->unit?->rent_amount ?? '0');
                $this->createServiceCharges[$application->id] ??= (string) ($application->unit?->service_charge_amount ?? '0');
                $this->createBillingCycles[$application->id] ??= $application->unit?->billing_cycle->value ?? BillingCycle::Yearly->value;
                $this->createNotes[$application->id] ??= '';
            }
        }

        return view('livewire.tenancies.index', [
            'tenancies' => $tenancies,
            'convertibleApplications' => $convertibleApplications,
            'tenantPortal' => $isTenant ? $this->tenantPortalData($user) : null,
            ...TenancyOptions::forForms(),
        ])->layout('components.layouts.app');
    }

    protected function tenantPortalData($user): array
    {
        $activeTenancy = Tenancy::query()
            ->with(['property', 'unit', 'media', 'activityLogs.user'])
            ->visibleTo($user)
            ->whereIn('status', [
                TenancyStatus::PendingActivation,
                TenancyStatus::Active,
                TenancyStatus::RenewalPending,
                TenancyStatus::Ending,
            ])
            ->latest('lease_start_date')
            ->first();

        $invoices = Invoice::query()->visibleTo($user);
        $payments = Payment::query()->visibleTo($user);
        $maintenance = MaintenanceRequest::query()->visibleTo($user);

        $leaseDaysRemaining = $activeTenancy?->lease_end_date
            ? max(0, now()->startOfDay()->diffInDays($activeTenancy->lease_end_date, false))
            : null;

        return [
            'activeTenancy' => $activeTenancy,
            'leaseDaysRemaining' => $leaseDaysRemaining,
            'outstandingBalance' => (clone $invoices)
                ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])
                ->sum('balance_amount'),
            'openInvoices' => (clone $invoices)
                ->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue])
                ->count(),
            'pendingPayments' => (clone $payments)
                ->where('status', PaymentStatus::PendingVerification)
                ->count(),
            'verifiedPayments' => (clone $payments)
                ->where('status', PaymentStatus::Verified)
                ->count(),
            'openMaintenance' => (clone $maintenance)
                ->whereIn('status', [
                    MaintenanceStatus::Open,
                    MaintenanceStatus::Assigned,
                    MaintenanceStatus::InProgress,
                    MaintenanceStatus::AwaitingConfirmation,
                ])
                ->count(),
            'latestInvoices' => Invoice::query()
                ->with(['tenancy.property'])
                ->visibleTo($user)
                ->latest('issue_date')
                ->limit(3)
                ->get(),
            'latestMaintenance' => MaintenanceRequest::query()
                ->with(['property', 'unit'])
                ->visibleTo($user)
                ->latest()
                ->limit(3)
                ->get(),
        ];
    }

    protected function validateCreatePayload(int $applicationId, RentalApplication $application): array
    {
        $payload = [
            'status' => $this->createStatus[$applicationId] ?? TenancyStatus::PendingActivation->value,
            'lease_start_date' => $this->createLeaseStartDates[$applicationId] ?? null,
            'lease_end_date' => $this->blankToNull($this->createLeaseEndDates[$applicationId] ?? null),
            'move_in_date' => $this->blankToNull($this->createMoveInDates[$applicationId] ?? null),
            'rent_amount' => $this->blankToNull($this->createRentAmounts[$applicationId] ?? null),
            'service_charge_amount' => $this->blankToNull($this->createServiceCharges[$applicationId] ?? null),
            'billing_cycle' => $this->createBillingCycles[$applicationId] ?? $application->unit?->billing_cycle->value,
            'notes' => $this->blankToNull($this->createNotes[$applicationId] ?? null),
        ];

        return validator($payload, $this->tenancyRules())->validate();
    }

    protected function validateUpdatePayload(int $tenancyId, Tenancy $tenancy): array
    {
        $payload = [
            'status' => $this->updateStatus[$tenancyId] ?? $tenancy->status->value,
            'lease_start_date' => $this->updateLeaseStartDates[$tenancyId] ?? $tenancy->lease_start_date?->toDateString(),
            'lease_end_date' => $this->blankToNull($this->updateLeaseEndDates[$tenancyId] ?? null),
            'move_in_date' => $this->blankToNull($this->updateMoveInDates[$tenancyId] ?? null),
            'rent_amount' => $this->updateRentAmounts[$tenancyId] ?? (string) $tenancy->rent_amount,
            'service_charge_amount' => $this->blankToNull($this->updateServiceCharges[$tenancyId] ?? null) ?? '0',
            'billing_cycle' => $this->updateBillingCycles[$tenancyId] ?? $tenancy->billing_cycle->value,
            'notes' => $this->blankToNull($this->updateNotes[$tenancyId] ?? null),
        ];

        return validator($payload, $this->tenancyRules())->validate();
    }

    protected function tenancyRules(): array
    {
        return [
            'status' => ['required', Rule::in(array_map(static fn (TenancyStatus $status): string => $status->value, TenancyStatus::cases()))],
            'lease_start_date' => ['required', 'date'],
            'lease_end_date' => ['nullable', 'date', 'after_or_equal:lease_start_date'],
            'move_in_date' => ['nullable', 'date', 'after_or_equal:lease_start_date'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'service_charge_amount' => ['nullable', 'numeric', 'min:0'],
            'billing_cycle' => ['required', Rule::in(array_map(static fn (BillingCycle $cycle): string => $cycle->value, BillingCycle::cases()))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function blankToNull(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }
}
