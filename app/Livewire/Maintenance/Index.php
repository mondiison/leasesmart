<?php

namespace App\Livewire\Maintenance;

use App\Actions\Maintenance\AddMaintenanceUpdateAction;
use App\Actions\Maintenance\CreateMaintenanceRequestAction;
use App\Enums\MaintenanceStatus;
use App\Livewire\Concerns\InteractsWithFluxToast;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Tenancy;
use App\Support\Maintenance\MaintenanceOptions;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use AuthorizesRequests, InteractsWithFluxToast, WithFileUploads;

    public ?int $property_id = null;
    public ?int $property_unit_id = null;
    public ?int $tenancy_id = null;
    public string $title = '';
    public string $description = '';
    public ?string $category = null;
    public string $priority = 'medium';
    public ?int $assigned_to = null;
    public array $attachments = [];

    public array $statusUpdates = [];
    public array $messages = [];
    public array $assigneeUpdates = [];
    public array $updateAttachments = [];
    public array $tenantResolutionNotes = [];

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $focus = '';

    public function mount(): void
    {
        $this->authorize('viewAny', MaintenanceRequest::class);
    }

    public function updatedTenancyId($value): void
    {
        if (! $value) {
            return;
        }

        $tenancy = Tenancy::query()->visibleTo(auth()->user())->find($value);

        if ($tenancy) {
            $this->property_id = $tenancy->property_id;
            $this->property_unit_id = $tenancy->property_unit_id;
        }
    }

    public function getCanSubmitProperty(): bool
    {
        return filled($this->property_id)
            && filled($this->title)
            && filled($this->description)
            && filled($this->priority);
    }

    public function removeAttachmentUpload(int $index): void
    {
        if (! isset($this->attachments[$index])) {
            return;
        }

        $this->attachments[$index]->delete();
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function removeUpdateAttachmentUpload(int $requestId, int $index): void
    {
        if (! isset($this->updateAttachments[$requestId][$index])) {
            return;
        }

        $this->updateAttachments[$requestId][$index]->delete();
        unset($this->updateAttachments[$requestId][$index]);
        $this->updateAttachments[$requestId] = array_values($this->updateAttachments[$requestId]);
    }

    public function createRequest(CreateMaintenanceRequestAction $createMaintenanceRequestAction): void
    {
        $this->authorize('create', MaintenanceRequest::class);

        $payload = $this->validate($this->rules());
        $payload['tenant_user_id'] = auth()->user()->hasRole('tenant') ? auth()->id() : null;

        $request = $createMaintenanceRequestAction->execute(auth()->user(), $payload, $this->attachments);

        $this->reset('property_id', 'property_unit_id', 'tenancy_id', 'title', 'description', 'category', 'attachments');
        $this->priority = 'medium';
        $this->assigned_to = null;
        $this->resetValidation();

        $this->toast("Maintenance request {$request->title} created.", 'Request Created');
    }

    public function addUpdate(int $requestId, AddMaintenanceUpdateAction $addMaintenanceUpdateAction): void
    {
        $request = MaintenanceRequest::query()
            ->with(['property.landlord', 'property.caretaker', 'tenantUser', 'updates.user'])
            ->visibleTo(auth()->user())
            ->findOrFail($requestId);

        $this->authorize('update', $request);

        $payload = validator([
            'status' => $this->statusUpdates[$requestId] ?? $request->status->value,
            'message' => $this->blankToNull($this->messages[$requestId] ?? null),
            'assigned_to' => $this->assigneeUpdates[$requestId] ?? $request->assigned_to,
        ], [
            'status' => ['required', Rule::in(array_map(static fn (MaintenanceStatus $status): string => $status->value, MaintenanceStatus::cases()))],
            'message' => ['nullable', 'string', 'max:1500'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ])->validate();

        $updated = $addMaintenanceUpdateAction->execute(auth()->user(), $request, $payload, $this->updateAttachments[$requestId] ?? []);

        $this->statusUpdates[$requestId] = $updated->status->value;
        $this->messages[$requestId] = '';
        $this->assigneeUpdates[$requestId] = $updated->assigned_to;
        unset($this->updateAttachments[$requestId]);

        $this->toast('Maintenance request updated.', 'Request Updated');
    }

    public function confirmResolution(int $requestId, AddMaintenanceUpdateAction $addMaintenanceUpdateAction): void
    {
        $request = MaintenanceRequest::query()
            ->with(['property.landlord', 'property.caretaker', 'tenantUser', 'updates.user'])
            ->visibleTo(auth()->user())
            ->findOrFail($requestId);

        abort_unless(auth()->user()->hasRole('tenant') && $request->tenant_user_id === auth()->id(), 403);
        abort_unless($request->status === MaintenanceStatus::Resolved, 422);

        $addMaintenanceUpdateAction->execute(auth()->user(), $request, [
            'status' => MaintenanceStatus::Closed->value,
            'message' => $this->blankToNull($this->tenantResolutionNotes[$requestId] ?? null) ?? 'Tenant confirmed the issue is resolved.',
            'assigned_to' => $request->assigned_to,
        ]);

        $this->tenantResolutionNotes[$requestId] = '';
        $this->toast('Maintenance request closed. Thank you for confirming.', 'Resolution Confirmed');
    }

    public function reopenRequest(int $requestId, AddMaintenanceUpdateAction $addMaintenanceUpdateAction): void
    {
        $request = MaintenanceRequest::query()
            ->with(['property.landlord', 'property.caretaker', 'tenantUser', 'updates.user'])
            ->visibleTo(auth()->user())
            ->findOrFail($requestId);

        abort_unless(auth()->user()->hasRole('tenant') && $request->tenant_user_id === auth()->id(), 403);
        abort_unless(in_array($request->status, [MaintenanceStatus::Resolved, MaintenanceStatus::AwaitingConfirmation], true), 422);

        $addMaintenanceUpdateAction->execute(auth()->user(), $request, [
            'status' => MaintenanceStatus::Open->value,
            'message' => $this->blankToNull($this->tenantResolutionNotes[$requestId] ?? null) ?? 'Tenant reopened the request because the issue still needs attention.',
            'assigned_to' => $request->assigned_to,
        ]);

        $this->tenantResolutionNotes[$requestId] = '';
        $this->toast('Maintenance request reopened for follow-up.', 'Request Reopened');
    }

    public function render()
    {
        $user = auth()->user();
        $options = MaintenanceOptions::for($user);

        $requests = MaintenanceRequest::query()
            ->with(['property', 'unit', 'tenantUser', 'assignee', 'updates.user', 'media', 'activityLogs.user'])
            ->visibleTo($user)
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->focus === 'open', fn ($query) => $query->whereIn('status', [
                MaintenanceStatus::Open,
                MaintenanceStatus::Assigned,
                MaintenanceStatus::InProgress,
                MaintenanceStatus::AwaitingConfirmation,
            ]))
            ->when($this->focus === 'urgent', fn ($query) => $query->where('priority', 'urgent'))
            ->when(trim($this->search) !== '', function ($query) {
                $search = '%'.trim($this->search).'%';

                $query->where(function ($inner) use ($search) {
                    $inner
                        ->where('title', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('category', 'like', $search)
                        ->orWhereHas('property', fn ($propertyQuery) => $propertyQuery->where('title', 'like', $search))
                        ->orWhereHas('unit', fn ($unitQuery) => $unitQuery->where('unit_name', 'like', $search))
                        ->orWhereHas('tenantUser', fn ($tenantQuery) => $tenantQuery->where('name', 'like', $search)->orWhere('email', 'like', $search));
                });
            })
            ->latest('reported_at')
            ->limit(12)
            ->get();

        foreach ($requests as $request) {
            $this->statusUpdates[$request->id] ??= $request->status->value;
            $this->messages[$request->id] ??= '';
            $this->assigneeUpdates[$request->id] ??= $request->assigned_to;
            $this->tenantResolutionNotes[$request->id] ??= '';
        }

        return view('livewire.maintenance.index', [
            'requests' => $requests,
            'isTenant' => $user->hasRole('tenant'),
            ...$options,
        ])->layout('components.layouts.app');
    }

    protected function rules(): array
    {
        return [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'property_unit_id' => ['nullable', 'integer', 'exists:property_units,id'],
            'tenancy_id' => ['nullable', 'integer', 'exists:tenancies,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2500'],
            'category' => ['nullable', 'string', 'max:120'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'attachments' => ['array'],
            'attachments.*' => ['file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }

    protected function blankToNull(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }
}
