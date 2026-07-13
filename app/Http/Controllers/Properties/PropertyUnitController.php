<?php

namespace App\Http\Controllers\Properties;

use App\Actions\Properties\CreatePropertyUnitAction;
use App\Actions\Properties\UpdatePropertyUnitAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Properties\StorePropertyUnitRequest;
use App\Http\Requests\Properties\UpdatePropertyUnitRequest;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Support\Properties\PropertyOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PropertyUnitController extends Controller
{
    public function create(Property $property): View
    {
        $this->authorize('create', [PropertyUnit::class, $property]);

        return view('properties.units.create', [
            'property' => $property,
            'unit' => new PropertyUnit(),
            'selectedAmenities' => old('amenity_ids', []),
            ...PropertyOptions::forForms(),
        ]);
    }

    public function store(StorePropertyUnitRequest $request, Property $property, CreatePropertyUnitAction $createUnit): RedirectResponse
    {
        $createUnit->execute($request->user(), $property, $request->validated());

        return redirect()->route('properties.edit', $property)->with('status', 'Unit created successfully.');
    }

    public function edit(Property $property, PropertyUnit $unit): View
    {
        abort_unless($unit->property_id === $property->getKey(), 404);
        $this->authorize('update', $unit);

        return view('properties.units.edit', [
            'property' => $property,
            'unit' => $unit->load(['amenities', 'media']),
            'selectedAmenities' => old('amenity_ids', $unit->amenities->modelKeys()),
            ...PropertyOptions::forForms(),
        ]);
    }

    public function update(UpdatePropertyUnitRequest $request, Property $property, PropertyUnit $unit, UpdatePropertyUnitAction $updateUnit): RedirectResponse
    {
        abort_unless($unit->property_id === $property->getKey(), 404);
        $updateUnit->execute($request->user(), $unit, $request->validated());

        return redirect()->route('properties.edit', $property)->with('status', 'Unit updated successfully.');
    }
}
