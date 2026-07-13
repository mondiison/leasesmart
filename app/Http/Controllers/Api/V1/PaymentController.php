<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $payments = Payment::query()
            ->visibleTo($request->user())
            ->with(['invoice', 'tenancy', 'receipt'])
            ->latest('paid_at')
            ->paginate($request->integer('per_page', 15));

        return PaymentResource::collection($payments);
    }
}
