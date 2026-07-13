<?php

namespace App\Livewire\Properties;

use App\Actions\Properties\CreatePropertyUnitAction;
use App\Actions\Properties\UpdatePropertyUnitAction;
use App\Livewire\Concerns\InteractsWithFluxToast;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Support\Properties\PropertyOptions;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ManageUnit extends Component
{
    use AuthorizesRequests, InteractsWithFluxToast, WithFileUploads;

    public Property $property;
    public ?PropertyUnit $unit = null;

    public ?string $unit_code = null;
    public string $unit_name = '';
    public ?string $unit_type = null;
    public ?string $floor_label = null;
    public ?string $bedrooms = null;
    public ?string $bathrooms = null;
    public ?string $toilets = null;
    public ?string $size_sqm = null;
    public string $occupancy_status = 'vacant';
    public string $rent_amount = '';
    public string $billing_cycle = 'yearly';
    public ?string $service_charge_amount = null;
    public ?string $caution_fee_amount = null;
    public ?string $inspection_fee_amount = null;
    public ?string $available_from = null;
    public ?string $description = null;
    public bool $is_listed = true;
    public array $amenity_ids = [];
    public array $media = [];

    public function mount(Property $property, ?PropertyUnit $unit = null): void
    {
        $this->property = $property;

        if ($unit?->exists) {
            abort_unless($unit->property_id === $property->getKey(), 404);
            $this->authorize('update', $unit);
            $this->unit = $unit->load(['amenities', 'media', 'property']);
            $this->fillFromUnit($this->unit);

            return;
        }

        $this->authorize('create', [PropertyUnit::class, $property]);
    }

    protected function rules(): array
    {
        $options = PropertyOptions::forForms();

        return [
            'unit_code' => ['nullable', 'string', 'max:100', Rule::unique('property_units', 'unit_code')->ignore($this->unit)],
            'unit_name' => ['required', 'string', 'max:255'],
            'unit_type' => ['nullable', 'string', 'max:120'],
            'floor_label' => ['nullable', 'string', 'max:120'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'toilets' => ['nullable', 'integer', 'min:0', 'max:50'],
            'size_sqm' => ['nullable', 'numeric', 'min:0'],
            'occupancy_status' => ['required', Rule::in(collect($options['occupancyStatuses'])->map->value->all())],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', Rule::in(collect($options['billingCycles'])->map->value->all())],
            'service_charge_amount' => ['nullable', 'numeric', 'min:0'],
            'caution_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'inspection_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'available_from' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'is_listed' => ['boolean'],
            'amenity_ids' => ['array'],
            'amenity_ids.*' => ['integer', 'exists:property_amenities,id'],
            'media' => ['array'],
            'media.*' => ['image', 'max:5120'],
        ];
    }

    public function updated(string $property): void
    {
        if (
            in_array($property, ['unit_name', 'unit_code', 'occupancy_status', 'rent_amount', 'billing_cycle', 'available_from'], true)
            || str_starts_with($property, 'media.')
        ) {
            $this->validateOnly($property);
        }
    }

    public function getCanSubmitProperty(): bool
    {
        return filled($this->unit_name)
            && filled($this->occupancy_status)
            && filled($this->rent_amount)
            && filled($this->billing_cycle);
    }

    public function removeMediaUpload(int $index): void
    {
        if (! isset($this->media[$index])) {
            return;
        }

        $this->media[$index]->delete();
        unset($this->media[$index]);
        $this->media = array_values($this->media);
    }

    public function setGalleryCover(int $mediaId): void
    {
        if (! $this->unit?->exists) {
            return;
        }

        $this->authorize('update', $this->unit);

        $mediaIds = $this->unit
            ->media()
            ->where('collection_name', 'gallery')
            ->orderBy('order_column')
            ->pluck('id')
            ->all();

        if (! in_array($mediaId, $mediaIds, true)) {
            return;
        }

        Media::setNewOrder([
            $mediaId,
            ...array_values(array_diff($mediaIds, [$mediaId])),
        ]);

        $this->unit = $this->unit->fresh(['amenities', 'media', 'property']);
        $this->toast('Unit cover image updated.');
    }

    public function deleteGalleryImage(int $mediaId): void
    {
        if (! $this->unit?->exists) {
            return;
        }

        $this->authorize('update', $this->unit);

        $media = $this->unit
            ->media()
            ->where('collection_name', 'gallery')
            ->whereKey($mediaId)
            ->first();

        if (! $media) {
            return;
        }

        $media->delete();
        $this->unit = $this->unit->fresh(['amenities', 'media', 'property']);
        $this->toast('Unit image removed.');
    }

    public function save(CreatePropertyUnitAction $createUnit, UpdatePropertyUnitAction $updateUnit)
    {
        $validated = $this->validate();

        if ($this->unit?->exists) {
            $updateUnit->execute(auth()->user(), $this->unit, $validated);
            $this->toast('Unit updated successfully.');

            return $this->redirectRoute('properties.edit', $this->property, navigate: true);
        }

        $createUnit->execute(auth()->user(), $this->property, $validated);
        $this->toast('Unit created successfully.');

        return $this->redirectRoute('properties.edit', $this->property, navigate: true);
    }

    public function render()
    {
        return view('livewire.properties.manage-unit', [
            'unitRecord' => $this->unit?->loadMissing(['amenities', 'media']),
            ...PropertyOptions::forForms(),
        ])->layout('components.layouts.app');
    }

    protected function fillFromUnit(PropertyUnit $unit): void
    {
        $this->unit_code = $unit->unit_code;
        $this->unit_name = $unit->unit_name;
        $this->unit_type = $unit->unit_type;
        $this->floor_label = $unit->floor_label;
        $this->bedrooms = $unit->bedrooms !== null ? (string) $unit->bedrooms : null;
        $this->bathrooms = $unit->bathrooms !== null ? (string) $unit->bathrooms : null;
        $this->toilets = $unit->toilets !== null ? (string) $unit->toilets : null;
        $this->size_sqm = $unit->size_sqm !== null ? (string) $unit->size_sqm : null;
        $this->occupancy_status = $unit->occupancy_status->value;
        $this->rent_amount = (string) $unit->rent_amount;
        $this->billing_cycle = $unit->billing_cycle->value;
        $this->service_charge_amount = $unit->service_charge_amount !== null ? (string) $unit->service_charge_amount : null;
        $this->caution_fee_amount = $unit->caution_fee_amount !== null ? (string) $unit->caution_fee_amount : null;
        $this->inspection_fee_amount = $unit->inspection_fee_amount !== null ? (string) $unit->inspection_fee_amount : null;
        $this->available_from = $unit->available_from?->format('Y-m-d');
        $this->description = $unit->description;
        $this->is_listed = $unit->is_listed;
        $this->amenity_ids = $unit->amenities->modelKeys();
    }
}
