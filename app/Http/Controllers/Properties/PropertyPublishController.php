<?php

namespace App\Http\Controllers\Properties;

use App\Actions\Properties\UpdatePropertyPublishStatusAction;
use App\Enums\PropertyPublishStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Properties\UpdatePropertyPublishStatusRequest;
use App\Models\Property;
use DomainException;
use Illuminate\Http\RedirectResponse;

class PropertyPublishController extends Controller
{
    public function __invoke(UpdatePropertyPublishStatusRequest $request, Property $property, UpdatePropertyPublishStatusAction $updatePublishStatus): RedirectResponse
    {
        try {
            $updatePublishStatus->execute(
                $request->user(),
                $property,
                PropertyPublishStatus::from($request->validated('publish_status')),
            );
        } catch (DomainException $exception) {
            return redirect()->route('properties.edit', $property)->withErrors([
                'publish_status' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('properties.edit', $property)->with('status', 'Property publication status updated.');
    }
}
