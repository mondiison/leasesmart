<?php

namespace App\Livewire\Marketplace;

use App\Actions\Applications\CreateRentalApplicationAction;
use App\Livewire\Concerns\InteractsWithFluxToast;
use App\Models\Inspection;
use App\Models\Property;
use App\Support\Applications\RentalApplicationOptions;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class RentalApplicationForm extends Component
{
    use InteractsWithFluxToast, WithFileUploads;

    public int $propertyId;

    public ?int $property_unit_id = null;
    public ?int $inspection_id = null;
    public string $applicant_name = '';
    public string $applicant_email = '';
    public string $applicant_phone = '';
    public ?string $employment_status = null;
    public ?string $employer_name = null;
    public ?string $monthly_income = null;
    public ?string $preferred_move_in_date = null;
    public ?string $message = null;
    public array $documents = [];

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
            'inspection_id' => ['nullable', Rule::exists('inspections', 'id')],
            'applicant_name' => ['required', 'string', 'max:255'],
            'applicant_email' => ['required', 'email', 'max:255'],
            'applicant_phone' => ['required', 'string', 'max:40'],
            'employment_status' => ['nullable', 'string', 'max:120'],
            'employer_name' => ['nullable', 'string', 'max:255'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'preferred_move_in_date' => ['nullable', 'date', 'after_or_equal:today'],
            'message' => ['nullable', 'string', 'max:1500'],
            'documents' => ['array'],
            'documents.*' => ['file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }

    public function updated(string $property): void
    {
        if (
            in_array($property, ['property_unit_id', 'inspection_id', 'applicant_name', 'applicant_email', 'applicant_phone', 'employment_status', 'employer_name', 'monthly_income', 'preferred_move_in_date', 'message'], true)
            || str_starts_with($property, 'documents.')
        ) {
            $this->validateOnly($property);
        }
    }

    public function getCanSubmitProperty(): bool
    {
        return $this->property_unit_id !== null
            && filled($this->applicant_name)
            && filled($this->applicant_email)
            && filled($this->applicant_phone);
    }

    public function removeDocumentUpload(int $index): void
    {
        if (! isset($this->documents[$index])) {
            return;
        }

        $this->documents[$index]->delete();
        unset($this->documents[$index]);
        $this->documents = array_values($this->documents);
    }

    public function save(CreateRentalApplicationAction $createRentalApplication): void
    {
        $validated = $this->validate();
        $property = $this->property();

        $createRentalApplication->execute($property, $validated);

        $this->resetForm($property);
        $this->resetValidation();
        $this->toast('Rental application submitted. The review team can now process it from the applications queue.', 'Application Submitted');
    }

    public function render()
    {
        $property = $this->property();
        $inspectionOptions = Inspection::query()
            ->where('property_id', $property->getKey())
            ->where('requester_email', $this->applicant_email)
            ->whereIn('property_unit_id', $property->publicUnits->modelKeys())
            ->latest()
            ->get();

        return view('livewire.marketplace.rental-application-form', [
            'propertyRecord' => $property,
            'inspectionOptions' => $inspectionOptions,
            ...RentalApplicationOptions::forForms(),
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
        $this->reset(
            'property_unit_id',
            'inspection_id',
            'applicant_name',
            'applicant_email',
            'applicant_phone',
            'employment_status',
            'employer_name',
            'monthly_income',
            'preferred_move_in_date',
            'message',
            'documents',
        );

        $this->property_unit_id = $property->publicUnits()->orderBy('rent_amount')->value('id');

        if (auth()->check()) {
            $this->applicant_name = auth()->user()->name;
            $this->applicant_email = auth()->user()->email;
            $this->applicant_phone = auth()->user()->phone ?? '';
        }
    }
}
