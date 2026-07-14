<?php

namespace App\Livewire\Applications;

use App\Actions\Applications\UpdateRentalApplicationAction;
use App\Livewire\Concerns\InteractsWithFluxToast;
use App\Models\RentalApplication;
use App\Support\Applications\RentalApplicationOptions;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests, InteractsWithFluxToast, WithPagination;

    #[Url]
    public string $status = '';

    #[Url(as: 'q')]
    public string $search = '';

    /**
     * @var array<int, string>
     */
    public array $statusUpdates = [];

    /**
     * @var array<int, string>
     */
    public array $reviewNotes = [];

    /**
     * @var array<int, string>
     */
    public array $agentFeeAmounts = [];

    /**
     * @var array<int, string>
     */
    public array $legalFeeAmounts = [];

    public function mount(): void
    {
        $this->authorize('viewAny', RentalApplication::class);
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function save(int $applicationId, UpdateRentalApplicationAction $updateRentalApplicationAction): void
    {
        $application = RentalApplication::query()->with(['property.landlord', 'unit', 'applicant'])->findOrFail($applicationId);
        $this->authorize('update', $application);

        $payload = [
            'status' => $this->statusUpdates[$applicationId] ?? $application->status->value,
            'review_notes' => $this->reviewNotes[$applicationId] ?? null,
            'agent_fee_amount' => $this->agentFeeAmounts[$applicationId] ?? 0,
            'legal_fee_amount' => $this->legalFeeAmounts[$applicationId] ?? 0,
        ];

        validator($payload, [
            'status' => ['required'],
            'review_notes' => ['nullable', 'string', 'max:2500'],
            'agent_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'legal_fee_amount' => ['nullable', 'numeric', 'min:0'],
        ])->validate();

        $updated = $updateRentalApplicationAction->execute(auth()->user(), $application, $payload);

        $this->statusUpdates[$applicationId] = $updated->status->value;
        $this->reviewNotes[$applicationId] = $updated->review_notes ?? '';
        $this->agentFeeAmounts[$applicationId] = (string) $updated->agent_fee_amount;
        $this->legalFeeAmounts[$applicationId] = (string) $updated->legal_fee_amount;

        $this->toast('Rental application updated successfully.');
    }

    public function render()
    {
        $query = RentalApplication::query()
            ->with(['property', 'unit', 'reviewer', 'media', 'activityLogs.user'])
            ->visibleTo(auth()->user())
            ->latest('submitted_at');

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if (trim($this->search) !== '') {
            $search = '%'.trim($this->search).'%';

            $query->where(function ($inner) use ($search) {
                $inner
                    ->where('applicant_name', 'like', $search)
                    ->orWhere('applicant_email', 'like', $search)
                    ->orWhere('applicant_phone', 'like', $search)
                    ->orWhere('message', 'like', $search)
                    ->orWhereHas('property', fn ($propertyQuery) => $propertyQuery->where('title', 'like', $search))
                    ->orWhereHas('unit', fn ($unitQuery) => $unitQuery->where('unit_name', 'like', $search));
            });
        }

        $applications = $query->paginate(10);

        foreach ($applications as $application) {
            $this->statusUpdates[$application->id] ??= $application->status->value;
            $this->reviewNotes[$application->id] ??= $application->review_notes ?? '';
            $this->agentFeeAmounts[$application->id] ??= (string) $application->agent_fee_amount;
            $this->legalFeeAmounts[$application->id] ??= (string) $application->legal_fee_amount;
        }

        return view('livewire.applications.index', [
            'applications' => $applications,
            'isTenant' => auth()->user()->hasRole('tenant'),
            ...RentalApplicationOptions::forForms(),
        ])->layout('components.layouts.app');
    }
}
