<?php

namespace App\Support\Reports;

use App\Enums\InvoiceStatus;
use App\Enums\MaintenanceStatus;
use App\Enums\PaymentStatus;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\RentalApplication;
use App\Models\Tenancy;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PremiumOperationalReport
{
    public const TYPES = [
        'applications',
        'tenancies',
        'maintenance',
        'inspections',
        'billing-invoices',
        'billing-payments',
    ];

    /**
     * @return array<string, mixed>
     */
    public function for(User $user, string $type, Request $request): array
    {
        $records = $this->records($user, $type, $request);

        return [
            'type' => $type,
            'title' => $this->title($type),
            'subtitle' => 'Premium operational report generated from LeaseSmart.',
            'generatedAt' => now(),
            'filters' => [
                'Search' => (string) $request->query('q', 'All'),
                'Status' => (string) $request->query('status', 'All'),
                'Focus' => (string) $request->query('focus', 'All'),
            ],
            'metrics' => $this->metrics($type, $records),
            'charts' => $this->charts($type, $records),
            'rows' => $this->rows($type, $records),
        ];
    }

    protected function records(User $user, string $type, Request $request): Collection
    {
        return match ($type) {
            'applications' => $this->applications($user, $request),
            'tenancies' => $this->tenancies($user, $request),
            'maintenance' => $this->maintenance($user, $request),
            'inspections' => $this->inspections($user, $request),
            'billing-invoices' => $this->invoices($user, $request),
            'billing-payments' => $this->payments($user, $request),
            default => collect(),
        };
    }

    protected function applications(User $user, Request $request): Collection
    {
        return RentalApplication::query()
            ->with(['property', 'unit'])
            ->visibleTo($user)
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->query('status')))
            ->when(trim((string) $request->query('q')) !== '', fn (Builder $query) => $this->searchApplications($query, (string) $request->query('q')))
            ->latest('submitted_at')
            ->limit(250)
            ->get();
    }

    protected function tenancies(User $user, Request $request): Collection
    {
        return Tenancy::query()
            ->with(['property', 'unit'])
            ->visibleTo($user)
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->query('status')))
            ->when($request->query('focus') === 'expiring', fn (Builder $query) => $query
                ->whereNotNull('lease_end_date')
                ->whereDate('lease_end_date', '>=', today())
                ->whereDate('lease_end_date', '<=', today()->addDays(60)))
            ->when(trim((string) $request->query('q')) !== '', fn (Builder $query) => $this->searchTenancies($query, (string) $request->query('q')))
            ->latest()
            ->limit(250)
            ->get();
    }

    protected function maintenance(User $user, Request $request): Collection
    {
        return MaintenanceRequest::query()
            ->with(['property', 'unit', 'tenantUser'])
            ->visibleTo($user)
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->query('status')))
            ->when($request->query('focus') === 'open', fn (Builder $query) => $query->whereIn('status', [
                MaintenanceStatus::Open,
                MaintenanceStatus::Assigned,
                MaintenanceStatus::InProgress,
                MaintenanceStatus::AwaitingConfirmation,
            ]))
            ->when($request->query('focus') === 'urgent', fn (Builder $query) => $query->where('priority', 'urgent'))
            ->when(trim((string) $request->query('q')) !== '', fn (Builder $query) => $this->searchMaintenance($query, (string) $request->query('q')))
            ->latest('reported_at')
            ->limit(250)
            ->get();
    }

    protected function inspections(User $user, Request $request): Collection
    {
        return Inspection::query()
            ->with(['property', 'unit'])
            ->visibleTo($user)
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->query('status')))
            ->when($request->query('focus') === 'upcoming', fn (Builder $query) => $query->whereDate('requested_for_date', '>=', today()))
            ->when(trim((string) $request->query('q')) !== '', fn (Builder $query) => $this->searchInspections($query, (string) $request->query('q')))
            ->latest()
            ->limit(250)
            ->get();
    }

    protected function invoices(User $user, Request $request): Collection
    {
        return Invoice::query()
            ->with(['tenantUser', 'tenancy.property'])
            ->visibleTo($user)
            ->when($request->query('focus') === 'overdue', fn (Builder $query) => $query->where('status', InvoiceStatus::Overdue))
            ->when($request->query('focus') === 'due_soon', fn (Builder $query) => $query
                ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])
                ->whereDate('due_date', '<=', today()->addDays(7)))
            ->when(trim((string) $request->query('q')) !== '', fn (Builder $query) => $this->searchInvoices($query, (string) $request->query('q')))
            ->latest('issue_date')
            ->limit(250)
            ->get();
    }

    protected function payments(User $user, Request $request): Collection
    {
        return Payment::query()
            ->with(['tenantUser', 'invoice', 'tenancy.property'])
            ->visibleTo($user)
            ->when($request->query('focus') === 'pending_payments', fn (Builder $query) => $query->where('status', PaymentStatus::PendingVerification))
            ->when(trim((string) $request->query('q')) !== '', fn (Builder $query) => $this->searchPayments($query, (string) $request->query('q')))
            ->latest()
            ->limit(250)
            ->get();
    }

    protected function metrics(string $type, Collection $records): array
    {
        return match ($type) {
            'billing-invoices' => [
                ['label' => 'Invoices', 'value' => number_format($records->count())],
                ['label' => 'Total Amount', 'value' => 'NGN '.number_format((float) $records->sum('total_amount'), 2)],
                ['label' => 'Open Balance', 'value' => 'NGN '.number_format((float) $records->sum('balance_amount'), 2)],
                ['label' => 'Overdue', 'value' => number_format($records->where('status', InvoiceStatus::Overdue)->count())],
            ],
            'billing-payments' => [
                ['label' => 'Payments', 'value' => number_format($records->count())],
                ['label' => 'Total Paid', 'value' => 'NGN '.number_format((float) $records->sum('amount'), 2)],
                ['label' => 'Pending', 'value' => number_format($records->where('status', PaymentStatus::PendingVerification)->count())],
                ['label' => 'Verified', 'value' => number_format($records->where('status', PaymentStatus::Verified)->count())],
            ],
            default => [
                ['label' => 'Records', 'value' => number_format($records->count())],
                ['label' => 'Open', 'value' => number_format($records->filter(fn ($record) => str_contains($record->status->value ?? '', 'open'))->count())],
                ['label' => 'Active', 'value' => number_format($records->filter(fn ($record) => str_contains($record->status->value ?? '', 'active'))->count())],
                ['label' => 'Updated', 'value' => now()->format('M j, Y')],
            ],
        };
    }

    protected function charts(string $type, Collection $records): array
    {
        $status = $records
            ->groupBy(fn ($record) => $record->status?->label() ?? 'Unknown')
            ->map(fn (Collection $items, string $label): array => ['label' => $label, 'value' => $items->count()])
            ->values()
            ->all();

        $secondary = match ($type) {
            'billing-invoices' => $records->groupBy(fn ($record) => $record->invoice_type?->label() ?? 'Unknown'),
            'maintenance' => $records->groupBy(fn ($record) => $record->priority?->label() ?? 'Unknown'),
            default => $records->groupBy(fn ($record) => $record->created_at?->format('M Y') ?? 'Current'),
        };

        return [
            ['title' => 'Status Mix', 'items' => $status],
            ['title' => $type === 'maintenance' ? 'Priority Mix' : ($type === 'billing-invoices' ? 'Invoice Type Mix' : 'Monthly Mix'), 'items' => $secondary->map(fn (Collection $items, string $label): array => ['label' => $label, 'value' => $items->count()])->values()->all()],
        ];
    }

    protected function rows(string $type, Collection $records): array
    {
        return $records->take(20)->map(function ($record) use ($type): array {
            return match ($type) {
                'applications' => [$record->applicant_name, $record->property?->title, $record->unit?->unit_name, $record->status->label(), 'NGN '.number_format((float) $record->agent_fee_amount + (float) $record->legal_fee_amount, 2)],
                'tenancies' => [$record->tenant_name, $record->property?->title, $record->unit?->unit_name, $record->status->label()],
                'maintenance' => [$record->title, $record->property?->title, $record->priority->label(), $record->status->label()],
                'inspections' => [$record->requester_name, $record->property?->title, $record->requested_for_date?->toDateString(), $record->status->label()],
                'billing-invoices' => [$record->invoice_number, $record->tenantUser?->name, 'NGN '.number_format((float) $record->balance_amount, 2), $record->status->label()],
                'billing-payments' => [$record->payment_reference, $record->tenantUser?->name, 'NGN '.number_format((float) $record->amount, 2), $record->status->label()],
                default => [],
            };
        })->all();
    }

    protected function title(string $type): string
    {
        return str($type)->replace('-', ' ')->headline()->toString();
    }

    protected function searchApplications(Builder $query, string $term): void
    {
        $like = '%'.trim($term).'%';
        $query->where(fn (Builder $inner) => $inner->where('applicant_name', 'like', $like)->orWhere('applicant_email', 'like', $like)->orWhereHas('property', fn (Builder $property) => $property->where('title', 'like', $like)));
    }

    protected function searchTenancies(Builder $query, string $term): void
    {
        $like = '%'.trim($term).'%';
        $query->where(fn (Builder $inner) => $inner->where('tenant_name', 'like', $like)->orWhere('tenant_email', 'like', $like)->orWhereHas('property', fn (Builder $property) => $property->where('title', 'like', $like)));
    }

    protected function searchMaintenance(Builder $query, string $term): void
    {
        $like = '%'.trim($term).'%';
        $query->where(fn (Builder $inner) => $inner->where('title', 'like', $like)->orWhere('description', 'like', $like)->orWhereHas('property', fn (Builder $property) => $property->where('title', 'like', $like)));
    }

    protected function searchInspections(Builder $query, string $term): void
    {
        $like = '%'.trim($term).'%';
        $query->where(fn (Builder $inner) => $inner->where('requester_name', 'like', $like)->orWhere('requester_email', 'like', $like)->orWhereHas('property', fn (Builder $property) => $property->where('title', 'like', $like)));
    }

    protected function searchInvoices(Builder $query, string $term): void
    {
        $like = '%'.trim($term).'%';
        $query->where(fn (Builder $inner) => $inner->where('invoice_number', 'like', $like)->orWhereHas('tenantUser', fn (Builder $tenant) => $tenant->where('name', 'like', $like))->orWhereHas('tenancy.property', fn (Builder $property) => $property->where('title', 'like', $like)));
    }

    protected function searchPayments(Builder $query, string $term): void
    {
        $like = '%'.trim($term).'%';
        $query->where(fn (Builder $inner) => $inner->where('payment_reference', 'like', $like)->orWhereHas('tenantUser', fn (Builder $tenant) => $tenant->where('name', 'like', $like))->orWhereHas('invoice', fn (Builder $invoice) => $invoice->where('invoice_number', 'like', $like)));
    }
}
