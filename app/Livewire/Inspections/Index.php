<?php

namespace App\Livewire\Inspections;

use App\Actions\Inspections\UpdateInspectionAction;
use App\Livewire\Concerns\InteractsWithFluxToast;
use App\Models\Inspection;
use App\Support\Inspections\InspectionOptions;
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

    #[Url]
    public string $focus = '';

    /**
     * @var array<int, string>
     */
    public array $statusUpdates = [];

    /**
     * @var array<int, string>
     */
    public array $scheduledAts = [];

    /**
     * @var array<int, string>
     */
    public array $internalNotes = [];

    public function mount(): void
    {
        $this->authorize('viewAny', Inspection::class);
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

    public function save(int $inspectionId, UpdateInspectionAction $updateInspectionAction): void
    {
        $inspection = Inspection::query()->with(['property.landlord', 'property.caretaker'])->findOrFail($inspectionId);
        $this->authorize('update', $inspection);

        $payload = [
            'status' => $this->statusUpdates[$inspectionId] ?? $inspection->status->value,
            'scheduled_at' => ($this->scheduledAts[$inspectionId] ?? '') !== '' ? $this->scheduledAts[$inspectionId] : null,
            'internal_notes' => $this->internalNotes[$inspectionId] ?? null,
        ];

        validator($payload, [
            'status' => ['required'],
            'scheduled_at' => ['nullable', 'date'],
            'internal_notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        $updated = $updateInspectionAction->execute(auth()->user(), $inspection, $payload);

        $this->statusUpdates[$inspectionId] = $updated->status->value;
        $this->scheduledAts[$inspectionId] = $updated->scheduled_at?->format('Y-m-d\\TH:i') ?? '';
        $this->internalNotes[$inspectionId] = $updated->internal_notes ?? '';

        $this->toast('Inspection updated successfully.');
    }

    public function render()
    {
        $query = Inspection::query()
            ->with(['property', 'unit', 'handler'])
            ->visibleTo(auth()->user())
            ->latest();

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->focus === 'upcoming') {
            $query->whereDate('requested_for_date', '>=', today())
                ->orderBy('requested_for_date');
        }

        if (trim($this->search) !== '') {
            $search = '%'.trim($this->search).'%';

            $query->where(function ($inner) use ($search) {
                $inner
                    ->where('requester_name', 'like', $search)
                    ->orWhere('requester_email', 'like', $search)
                    ->orWhere('requester_phone', 'like', $search)
                    ->orWhere('message', 'like', $search)
                    ->orWhereHas('property', fn ($propertyQuery) => $propertyQuery->where('title', 'like', $search))
                    ->orWhereHas('unit', fn ($unitQuery) => $unitQuery->where('unit_name', 'like', $search));
            });
        }

        $inspections = $query->paginate(10);

        foreach ($inspections as $inspection) {
            $this->statusUpdates[$inspection->id] ??= $inspection->status->value;
            $this->scheduledAts[$inspection->id] ??= $inspection->scheduled_at?->format('Y-m-d\\TH:i') ?? '';
            $this->internalNotes[$inspection->id] ??= $inspection->internal_notes ?? '';
        }

        return view('livewire.inspections.index', [
            'inspections' => $inspections,
            ...InspectionOptions::forForms(),
        ])->layout('components.layouts.app');
    }
}
