<?php

namespace App\Livewire\Properties;

use App\Actions\Properties\CreatePropertyAction;
use App\Actions\Properties\UpdatePropertyAction;
use App\Actions\Properties\UpdatePropertyPublishStatusAction;
use App\Enums\PropertyPublishStatus;
use App\Livewire\Concerns\InteractsWithFluxToast;
use App\Models\Property;
use App\Support\Properties\PropertyOptions;
use DomainException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ManageProperty extends Component
{
    use AuthorizesRequests, InteractsWithFluxToast, WithFileUploads;

    public ?Property $property = null;

    public ?int $landlord_id = null;
    public ?int $caretaker_id = null;
    public string $title = '';
    public ?string $property_code = null;
    public string $property_type = 'apartment_building';
    public ?string $description = null;
    public string $address_line_1 = '';
    public ?string $address_line_2 = null;
    public string $city = '';
    public ?string $state = null;
    public string $country = 'Nigeria';
    public ?string $postal_code = null;
    public ?string $latitude = null;
    public ?string $longitude = null;
    public ?string $year_built = null;
    public string $publish_status = 'draft';
    public bool $is_featured = false;
    public array $amenity_ids = [];
    public array $media = [];

    public function mount(?Property $property = null): void
    {
        if ($property?->exists) {
            $this->authorize('update', $property);
            $this->property = $property->load(['amenities', 'landlord.user', 'caretaker.user', 'media', 'units.media', 'units.amenities']);
            $this->fillFromProperty($this->property);

            return;
        }

        $this->authorize('create', Property::class);
    }

    protected function rules(): array
    {
        $options = PropertyOptions::forForms();

        return [
            'landlord_id' => ['nullable', 'integer', 'exists:landlords,id'],
            'caretaker_id' => ['nullable', 'integer', 'exists:caretakers,id'],
            'title' => ['required', 'string', 'max:255'],
            'property_code' => ['nullable', 'string', 'max:100', Rule::unique('properties', 'property_code')->ignore($this->property)],
            'property_type' => ['required', Rule::in(collect($options['propertyTypes'])->map->value->all())],
            'description' => ['nullable', 'string'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['required', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'year_built' => ['nullable', 'integer', 'min:1800', 'max:'.(now()->year + 1)],
            'publish_status' => ['required', Rule::in(collect($options['publishStatuses'])->map->value->all())],
            'is_featured' => ['boolean'],
            'amenity_ids' => ['array'],
            'amenity_ids.*' => ['integer', 'exists:property_amenities,id'],
            'media' => ['array'],
            'media.*' => ['image', 'max:5120'],
        ];
    }

    public function updated(string $property): void
    {
        if (
            in_array($property, ['title', 'property_code', 'property_type', 'address_line_1', 'city', 'country', 'publish_status', 'latitude', 'longitude', 'year_built'], true)
            || str_starts_with($property, 'media.')
        ) {
            $this->validateOnly($property);
        }
    }

    public function getCanSubmitProperty(): bool
    {
        return filled($this->title)
            && filled($this->property_type)
            && filled($this->address_line_1)
            && filled($this->city)
            && filled($this->country)
            && filled($this->publish_status);
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
        if (! $this->property?->exists) {
            return;
        }

        $this->authorize('update', $this->property);

        $mediaIds = $this->property
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

        $this->property = $this->property->fresh(['amenities', 'landlord.user', 'caretaker.user', 'media', 'units.media', 'units.amenities']);
        $this->toast('Property cover image updated.');
    }

    public function deleteGalleryImage(int $mediaId): void
    {
        if (! $this->property?->exists) {
            return;
        }

        $this->authorize('update', $this->property);

        $media = $this->property
            ->media()
            ->where('collection_name', 'gallery')
            ->whereKey($mediaId)
            ->first();

        if (! $media) {
            return;
        }

        $media->delete();
        $this->property = $this->property->fresh(['amenities', 'landlord.user', 'caretaker.user', 'media', 'units.media', 'units.amenities']);
        $this->toast('Property image removed.');
    }

    public function save(CreatePropertyAction $createProperty, UpdatePropertyAction $updateProperty)
    {
        $validated = $this->validate();

        if ($this->property?->exists) {
            $property = $updateProperty->execute(auth()->user(), $this->property, $validated);
            $this->property = $property;
            $this->media = [];
            $this->resetValidation('media.*');
            $this->toast('Property updated successfully.');

            return $this->redirectRoute('properties.edit', $property, navigate: true);
        }

        $property = $createProperty->execute(auth()->user(), $validated);
        $this->toast('Property created successfully.');

        return $this->redirectRoute('properties.edit', $property, navigate: true);
    }

    public function updatePublishStatus(string $status, UpdatePropertyPublishStatusAction $updatePublishStatus)
    {
        if (! $this->property?->exists) {
            return null;
        }

        $this->authorize('publish', $this->property);

        try {
            $property = $updatePublishStatus->execute(auth()->user(), $this->property, PropertyPublishStatus::from($status));
            $this->property = $property->load(['amenities', 'landlord.user', 'caretaker.user', 'media', 'units.media', 'units.amenities']);
            $this->fillFromProperty($this->property);
            $this->toast('Property publication status updated.');
        } catch (DomainException $exception) {
            $this->addError('publish_status', $exception->getMessage());
        }

        return null;
    }

    public function render()
    {
        return view('livewire.properties.manage-property', [
            'propertyRecord' => $this->property?->loadMissing(['amenities', 'landlord.user', 'caretaker.user', 'media', 'units.media', 'units.amenities']),
            ...PropertyOptions::forForms(),
        ])->layout('components.layouts.app');
    }

    protected function fillFromProperty(Property $property): void
    {
        $this->landlord_id = $property->landlord_id;
        $this->caretaker_id = $property->caretaker_id;
        $this->title = $property->title;
        $this->property_code = $property->property_code;
        $this->property_type = $property->property_type->value;
        $this->description = $property->description;
        $this->address_line_1 = $property->address_line_1;
        $this->address_line_2 = $property->address_line_2;
        $this->city = $property->city;
        $this->state = $property->state;
        $this->country = $property->country;
        $this->postal_code = $property->postal_code;
        $this->latitude = $property->latitude !== null ? (string) $property->latitude : null;
        $this->longitude = $property->longitude !== null ? (string) $property->longitude : null;
        $this->year_built = $property->year_built !== null ? (string) $property->year_built : null;
        $this->publish_status = $property->publish_status->value;
        $this->is_featured = $property->is_featured;
        $this->amenity_ids = $property->amenities->modelKeys();
    }
}
