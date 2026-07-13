<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $invoices = Invoice::query()
            ->visibleTo($request->user())
            ->with(['items', 'tenancy.property'])
            ->latest('issue_date')
            ->paginate($request->integer('per_page', 15));

        return InvoiceResource::collection($invoices);
    }
}
