<?php

namespace App\Support\Exports;

use App\Enums\InspectionStatus;
use App\Enums\InvoiceStatus;
use App\Enums\MaintenanceStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\RentalApplication;
use App\Models\Tenancy;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OperationalCsvExport
{
    public const TYPES = [
        'applications',
        'tenancies',
        'maintenance',
        'inspections',
        'billing-invoices',
        'billing-payments',
    ];

    public function filename(string $type): string
    {
        return 'smartrent-'.$type.'-'.now()->format('Y-m-d-His').'.csv';
    }

    public function stream(User $user, string $type, Request $request): void
    {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, $this->headings($type));

        foreach ($this->rows($user, $type, $request) as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
    }

    /**
     * @return list<string>
     */
    protected function headings(string $type): array
    {
        return match ($type) {
            'applications' => ['Submitted', 'Applicant', 'Email', 'Phone', 'Property', 'Unit', 'Status', 'Agent Fee', 'Legal Fee', 'Preferred Move In', 'Reviewer'],
            'tenancies' => ['Tenant', 'Email', 'Phone', 'Property', 'Unit', 'Status', 'Lease Start', 'Lease End', 'Rent', 'Service Charge', 'Billing Cycle'],
            'maintenance' => ['Reported', 'Title', 'Property', 'Unit', 'Tenant', 'Category', 'Priority', 'Status', 'Assigned To'],
            'inspections' => ['Requested Date', 'Requester', 'Email', 'Phone', 'Property', 'Unit', 'Status', 'Scheduled At', 'Handler'],
            'billing-invoices' => ['Issue Date', 'Due Date', 'Invoice Number', 'Tenant', 'Property', 'Type', 'Status', 'Total', 'Balance'],
            'billing-payments' => ['Paid At', 'Reference', 'Tenant', 'Property', 'Invoice', 'Method', 'Status', 'Amount', 'Verified At'],
            default => [],
        };
    }

    /**
     * @return iterable<list<string>>
     */
    protected function rows(User $user, string $type, Request $request): iterable
    {
        return match ($type) {
            'applications' => $this->applicationRows($user, $request),
            'tenancies' => $this->tenancyRows($user, $request),
            'maintenance' => $this->maintenanceRows($user, $request),
            'inspections' => $this->inspectionRows($user, $request),
            'billing-invoices' => $this->invoiceRows($user, $request),
            'billing-payments' => $this->paymentRows($user, $request),
            default => [],
        };
    }

    protected function applicationRows(User $user, Request $request): iterable
    {
        $rows = [];
        $role = $user->primaryRole() ?? Role::Tenant;
        $query = $role === Role::Tenant
            ? RentalApplication::query()->where('applicant_user_id', $user->getKey())
            : RentalApplication::query()->visibleTo($user);

        $query
            ->with(['property', 'unit', 'reviewer'])
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->query('status')))
            ->when(trim((string) $request->query('q')) !== '', function (Builder $query) use ($request) {
                $search = '%'.trim((string) $request->query('q')).'%';

                $query->where(function (Builder $inner) use ($search) {
                    $inner
                        ->where('applicant_name', 'like', $search)
                        ->orWhere('applicant_email', 'like', $search)
                        ->orWhere('applicant_phone', 'like', $search)
                        ->orWhereHas('property', fn (Builder $propertyQuery) => $propertyQuery->where('title', 'like', $search))
                        ->orWhereHas('unit', fn (Builder $unitQuery) => $unitQuery->where('unit_name', 'like', $search));
                });
            })
            ->latest('submitted_at')
            ->chunk(200, function ($applications) use (&$rows) {
                foreach ($applications as $application) {
                    $rows[] = [
                        $application->submitted_at?->toDateTimeString() ?? '',
                        $application->applicant_name,
                        $application->applicant_email,
                        $application->applicant_phone ?? '',
                        $application->property?->title ?? '',
                        $application->unit?->unit_name ?? '',
                        $application->status->label(),
                        (string) $application->agent_fee_amount,
                        (string) $application->legal_fee_amount,
                        $application->preferred_move_in_date?->toDateString() ?? '',
                        $application->reviewer?->name ?? '',
                    ];
                }
            });

        return $rows;
    }

    protected function tenancyRows(User $user, Request $request): iterable
    {
        $rows = [];

        Tenancy::query()
            ->with(['property', 'unit'])
            ->visibleTo($user)
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->query('status')))
            ->when($request->query('focus') === 'expiring', fn (Builder $query) => $query
                ->whereNotNull('lease_end_date')
                ->whereDate('lease_end_date', '>=', today())
                ->whereDate('lease_end_date', '<=', today()->addDays(60)))
            ->when(trim((string) $request->query('q')) !== '', function (Builder $query) use ($request) {
                $search = '%'.trim((string) $request->query('q')).'%';

                $query->where(function (Builder $inner) use ($search) {
                    $inner
                        ->where('tenant_name', 'like', $search)
                        ->orWhere('tenant_email', 'like', $search)
                        ->orWhere('tenant_phone', 'like', $search)
                        ->orWhereHas('property', fn (Builder $propertyQuery) => $propertyQuery->where('title', 'like', $search))
                        ->orWhereHas('unit', fn (Builder $unitQuery) => $unitQuery->where('unit_name', 'like', $search));
                });
            })
            ->latest()
            ->chunk(200, function ($tenancies) use (&$rows) {
                foreach ($tenancies as $tenancy) {
                    $rows[] = [
                        $tenancy->tenant_name,
                        $tenancy->tenant_email,
                        $tenancy->tenant_phone ?? '',
                        $tenancy->property?->title ?? '',
                        $tenancy->unit?->unit_name ?? '',
                        $tenancy->status->label(),
                        $tenancy->lease_start_date?->toDateString() ?? '',
                        $tenancy->lease_end_date?->toDateString() ?? '',
                        (string) $tenancy->rent_amount,
                        (string) $tenancy->service_charge_amount,
                        $tenancy->billing_cycle->label(),
                    ];
                }
            });

        return $rows;
    }

    protected function maintenanceRows(User $user, Request $request): iterable
    {
        $rows = [];

        MaintenanceRequest::query()
            ->with(['property', 'unit', 'tenantUser', 'assignee'])
            ->visibleTo($user)
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->query('status')))
            ->when($request->query('focus') === 'open', fn (Builder $query) => $query->whereIn('status', [
                MaintenanceStatus::Open,
                MaintenanceStatus::Assigned,
                MaintenanceStatus::InProgress,
                MaintenanceStatus::AwaitingConfirmation,
            ]))
            ->when($request->query('focus') === 'urgent', fn (Builder $query) => $query->where('priority', 'urgent'))
            ->when(trim((string) $request->query('q')) !== '', function (Builder $query) use ($request) {
                $search = '%'.trim((string) $request->query('q')).'%';

                $query->where(function (Builder $inner) use ($search) {
                    $inner
                        ->where('title', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('category', 'like', $search)
                        ->orWhereHas('property', fn (Builder $propertyQuery) => $propertyQuery->where('title', 'like', $search))
                        ->orWhereHas('unit', fn (Builder $unitQuery) => $unitQuery->where('unit_name', 'like', $search))
                        ->orWhereHas('tenantUser', fn (Builder $tenantQuery) => $tenantQuery->where('name', 'like', $search)->orWhere('email', 'like', $search));
                });
            })
            ->latest('reported_at')
            ->chunk(200, function ($requests) use (&$rows) {
                foreach ($requests as $request) {
                    $rows[] = [
                        $request->reported_at?->toDateTimeString() ?? '',
                        $request->title,
                        $request->property?->title ?? '',
                        $request->unit?->unit_name ?? '',
                        $request->tenantUser?->name ?? '',
                        $request->category ?? '',
                        $request->priority->label(),
                        $request->status->label(),
                        $request->assignee?->name ?? '',
                    ];
                }
            });

        return $rows;
    }

    protected function inspectionRows(User $user, Request $request): iterable
    {
        $rows = [];

        Inspection::query()
            ->with(['property', 'unit', 'handler'])
            ->visibleTo($user)
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->query('status')))
            ->when($request->query('focus') === 'upcoming', fn (Builder $query) => $query->whereDate('requested_for_date', '>=', today()))
            ->when(trim((string) $request->query('q')) !== '', function (Builder $query) use ($request) {
                $search = '%'.trim((string) $request->query('q')).'%';

                $query->where(function (Builder $inner) use ($search) {
                    $inner
                        ->where('requester_name', 'like', $search)
                        ->orWhere('requester_email', 'like', $search)
                        ->orWhere('requester_phone', 'like', $search)
                        ->orWhereHas('property', fn (Builder $propertyQuery) => $propertyQuery->where('title', 'like', $search))
                        ->orWhereHas('unit', fn (Builder $unitQuery) => $unitQuery->where('unit_name', 'like', $search));
                });
            })
            ->latest()
            ->chunk(200, function ($inspections) use (&$rows) {
                foreach ($inspections as $inspection) {
                    $rows[] = [
                        $inspection->requested_for_date?->toDateString() ?? '',
                        $inspection->requester_name,
                        $inspection->requester_email,
                        $inspection->requester_phone ?? '',
                        $inspection->property?->title ?? '',
                        $inspection->unit?->unit_name ?? '',
                        $inspection->status->label(),
                        $inspection->scheduled_at?->toDateTimeString() ?? '',
                        $inspection->handler?->name ?? '',
                    ];
                }
            });

        return $rows;
    }

    protected function invoiceRows(User $user, Request $request): iterable
    {
        $rows = [];

        Invoice::query()
            ->with(['tenancy.property', 'tenantUser'])
            ->visibleTo($user)
            ->when($request->query('focus') === 'overdue', fn (Builder $query) => $query->where('status', InvoiceStatus::Overdue))
            ->when($request->query('focus') === 'due_soon', fn (Builder $query) => $query
                ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])
                ->whereDate('due_date', '<=', today()->addDays(7)))
            ->when(trim((string) $request->query('q')) !== '', function (Builder $query) use ($request) {
                $search = '%'.trim((string) $request->query('q')).'%';

                $query->where(function (Builder $inner) use ($search) {
                    $inner
                        ->where('invoice_number', 'like', $search)
                        ->orWhereHas('tenantUser', fn (Builder $tenantQuery) => $tenantQuery->where('name', 'like', $search)->orWhere('email', 'like', $search))
                        ->orWhereHas('tenancy.property', fn (Builder $propertyQuery) => $propertyQuery->where('title', 'like', $search));
                });
            })
            ->latest('issue_date')
            ->chunk(200, function ($invoices) use (&$rows) {
                foreach ($invoices as $invoice) {
                    $rows[] = [
                        $invoice->issue_date?->toDateString() ?? '',
                        $invoice->due_date?->toDateString() ?? '',
                        $invoice->invoice_number,
                        $invoice->tenantUser?->name ?? '',
                        $invoice->tenancy?->property?->title ?? '',
                        $invoice->invoice_type->label(),
                        $invoice->status->label(),
                        (string) $invoice->total_amount,
                        (string) $invoice->balance_amount,
                    ];
                }
            });

        return $rows;
    }

    protected function paymentRows(User $user, Request $request): iterable
    {
        $rows = [];

        Payment::query()
            ->with(['invoice', 'tenancy.property', 'tenantUser'])
            ->visibleTo($user)
            ->when($request->query('focus') === 'pending_payments', fn (Builder $query) => $query->where('status', PaymentStatus::PendingVerification))
            ->when(trim((string) $request->query('q')) !== '', function (Builder $query) use ($request) {
                $search = '%'.trim((string) $request->query('q')).'%';

                $query->where(function (Builder $inner) use ($search) {
                    $inner
                        ->where('payment_reference', 'like', $search)
                        ->orWhereHas('tenantUser', fn (Builder $tenantQuery) => $tenantQuery->where('name', 'like', $search)->orWhere('email', 'like', $search))
                        ->orWhereHas('invoice', fn (Builder $invoiceQuery) => $invoiceQuery->where('invoice_number', 'like', $search))
                        ->orWhereHas('tenancy.property', fn (Builder $propertyQuery) => $propertyQuery->where('title', 'like', $search));
                });
            })
            ->latest()
            ->chunk(200, function ($payments) use (&$rows) {
                foreach ($payments as $payment) {
                    $rows[] = [
                        $payment->paid_at?->toDateTimeString() ?? '',
                        $payment->payment_reference,
                        $payment->tenantUser?->name ?? '',
                        $payment->tenancy?->property?->title ?? '',
                        $payment->invoice?->invoice_number ?? '',
                        $payment->payment_method->label(),
                        $payment->status->label(),
                        (string) $payment->amount,
                        $payment->verified_at?->toDateTimeString() ?? '',
                    ];
                }
            });

        return $rows;
    }
}
