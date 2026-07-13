<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaintenanceRequestController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $requests = MaintenanceRequest::query()
            ->visibleTo($request->user())
            ->with(['property', 'unit', 'assignee', 'updates.user'])
            ->latest('reported_at')
            ->paginate($request->integer('per_page', 15));

        return MaintenanceRequestResource::collection($requests);
    }
}
