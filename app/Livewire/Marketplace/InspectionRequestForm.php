<?php

namespace App\Livewire\Marketplace;

use App\Actions\Inspections\CreateInspectionRequestAction;
use App\Livewire\Concerns\InteractsWithFluxToast;
use App\Models\Property;
use Illuminate\Validation\Rule;
use Livewire\Component;

class InspectionRequestForm extends Component
{
    use InteractsWithFluxToast;

    public int $propertyId;

    public ?int $property_unit_id = null;
    public string $requester_name = '';
    public string $requester_email = '';
    public string $requester_phone = '';
    public ?string $requested_for_date = null;
    public ?string $requested_for_time = null;
    public ?string $message = null;

    public function mount(Property $property): void
    {
        abort_unless($property->isPubliclyVisible(), 404);

        $this->propertyId = $property->getKey();
        $this->resetForm($property);
    }

    protected function rules(): array
    {
        return [
            'property_unit_id' => ['required', Rule::exists('property_units', 'id')],
            'requester_name' => ['required', 'string', 'max:255'],
            'requester_email' => ['required', 'email', 'max:255'],
            'requester_phone' => ['required', 'string', 'max:40'],
            'requested_for_date' => ['nullable', 'date', 'after_or_equal:today'],
            'requested_for_time' => ['nullable', 'date_format:H:i'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['property_unit_id', 'requester_name', 'requester_email', 'requester_phone', 'requested_for_date', 'requested_for_time', 'message'], true)) {
            $this->validateOnly($property);
        }
    }

    public function getCanSubmitProperty(): bool
    {
        return $this->property_unit_id !== null
            && filled($this->requester_name)
            && filled($this->requester_email)
            && filled($this->requester_phone);
    }

    public function save(CreateInspectionRequestAction $createInspectionRequest): void
    {
        $validated = $this->validate();
        $property = $this->property();

        $createInspectionRequest->execute($property, $validated);

        $this->resetForm($property);
        $this->resetValidation();
        $this->toast('Inspection request submitted. Our team can now follow up from the inspections queue.', 'Inspection Requested');
    }

    public function render()
    {
        return view('livewire.marketplace.inspection-request-form', [
            'propertyRecord' => $this->property(),
        ]);
    }

    protected function property(): Property
    {
        return Property::query()
            ->publiclyVisible()
            ->with('publicUnits')
            ->findOrFail($this->propertyId);
    }

    protected function resetForm(Property $property): void
    {
        $this->reset('property_unit_id', 'requester_name', 'requester_email', 'requester_phone', 'requested_for_date', 'requested_for_time', 'message');

        $this->property_unit_id = $property->publicUnits()->orderBy('rent_amount')->value('id');

        if (auth()->check()) {
            $this->requester_name = auth()->user()->name;
            $this->requester_email = auth()->user()->email;
            $this->requester_phone = auth()->user()->phone ?? '';
        }
    }
}
