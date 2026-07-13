<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TenancyResource;
use App\Models\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TenancyController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $tenancies = Tenancy::query()
            ->visibleTo($request->user())
            ->with(['property', 'unit'])
            ->latest('lease_start_date')
            ->paginate($request->integer('per_page', 15));

        return TenancyResource::collection($tenancies);
    }
}
