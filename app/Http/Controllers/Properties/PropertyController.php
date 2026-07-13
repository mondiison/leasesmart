<?php

namespace App\Http\Controllers\Properties;

use App\Actions\Properties\CreatePropertyAction;
use App\Actions\Properties\UpdatePropertyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Properties\StorePropertyRequest;
use App\Http\Requests\Properties\UpdatePropertyRequest;
use App\Models\Property;
use App\Support\Properties\PropertyOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PropertyController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Property::class);

        $query = Property::query()
            ->with(['landlord.user', 'caretaker.user', 'units'])
            ->withCount('units')
            ->latest();

        $user = request()->user();

        if ($user->hasRole('landlord') && $user->landlordProfile) {
            $query->where('landlord_id', $user->landlordProfile->getKey());
        }

        if ($user->hasRole('caretaker') && $user->caretakerProfile) {
            $query->where('caretaker_id', $user->caretakerProfile->getKey());
        }

        return view('properties.index', [
            'properties' => $query->paginate(12),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Property::class);

        return view('properties.create', [
            'property' => new Property(),
            'selectedAmenities' => old('amenity_ids', []),
            ...PropertyOptions::forForms(),
        ]);
    }

    public function store(StorePropertyRequest $request, CreatePropertyAction $createProperty): RedirectResponse
    {
        $property = $createProperty->execute($request->user(), $request->validated());

        return redirect()->route('properties.edit', $property)->with('status', 'Property created successfully.');
    }

    public function edit(Property $property): View
    {
        $this->authorize('update', $property);

        return view('properties.edit', [
            'property' => $property->load(['amenities', 'landlord.user', 'caretaker.user', 'media', 'units.media', 'units.amenities']),
            'selectedAmenities' => old('amenity_ids', $property->amenities->modelKeys()),
            ...PropertyOptions::forForms(),
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property, UpdatePropertyAction $updateProperty): RedirectResponse
    {
        $updateProperty->execute($request->user(), $property, $request->validated());

        return redirect()->route('properties.edit', $property)->with('status', 'Property updated successfully.');
    }
}
