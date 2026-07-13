<?php

namespace App\Support\Dashboard;

use App\Enums\InspectionStatus;
use App\Enums\InvoiceStatus;
use App\Enums\MaintenanceStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyPublishStatus;
use App\Enums\RentalApplicationStatus;
use App\Enums\Role;
use App\Enums\TenancyStatus;
use App\Enums\UnitOccupancyStatus;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\RentalApplication;
use App\Models\Tenancy;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardViewModel
{
    /**
     * @return array<string, mixed>
     */
    public function for(User $user): array
    {
        $role = $user->primaryRole() ?? Role::Tenant;

        return [
            'pageTitle' => 'Dashboard',
            'hero' => $this->heroFor($user, $role),
            'stats' => $this->statsFor($user, $role),
            'commandCenter' => $this->commandCenterFor($user, $role),
            'reportGroups' => $this->reportGroupsFor($user, $role),
            'focusItems' => $this->focusItemsFor($user, $role),
            'activity' => $this->recentActivityFor($user, $role),
            'role' => $role,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function heroFor(User $user, Role $role): array
    {
        return match ($role) {
            Role::Admin => [
                'eyebrow' => 'Platform Pulse',
                'title' => 'Live operating picture across the full LeaseSmart workspace.',
                'description' => 'Track user growth, listing readiness, leasing throughput, collections, and support workload from one role-aware control center.',
            ],
            Role::Landlord => [
                'eyebrow' => 'Portfolio View',
                'title' => 'See leasing, collections, and service delivery across your portfolio.',
                'description' => 'This dashboard rolls up occupancy, application momentum, unpaid balances, and maintenance pressure for only the properties you own.',
            ],
            Role::Caretaker => [
                'eyebrow' => 'Operations Desk',
                'title' => 'Stay ahead of inspections, maintenance, and unit readiness.',
                'description' => 'Your dashboard is scoped to the properties you manage so the next site task, unresolved issue, and occupied stock are easy to spot.',
            ],
            Role::Tenant => [
                'eyebrow' => 'Tenant Portal',
                'title' => 'Keep rent, support, and tenancy updates within easy reach.',
                'description' => 'See what you owe, what is in progress, and where your current tenancy stands without leaving the dashboard.',
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    protected function statsFor(User $user, Role $role): array
    {
        $properties = $this->propertiesQuery($user);
        $units = $this->unitsQuery($user);
        $inspections = $this->inspectionsQuery($user);
        $applications = $this->applicationsQuery($user, $role);
        $tenancies = $this->tenanciesQuery($user);
        $invoices = $this->invoicesQuery($user);
        $payments = $this->paymentsQuery($user, $role);
        $maintenance = $this->maintenanceQuery($user);

        return match ($role) {
            Role::Admin => [
                [
                    'label' => 'Active Users',
                    'value' => $this->number(User::query()->where('is_active', true)->count()),
                    'detail' => $this->number(User::query()->where('is_active', false)->count()).' inactive accounts still on record.',
                ],
                [
                    'label' => 'Published Properties',
                    'value' => $this->number((clone $properties)->where('publish_status', PropertyPublishStatus::Published)->count()),
                    'detail' => $this->number((clone $units)->where('occupancy_status', UnitOccupancyStatus::Occupied)->count()).' occupied units across the platform.',
                ],
                [
                    'label' => 'Open Work Items',
                    'value' => $this->number(
                        (clone $inspections)->whereIn('status', [InspectionStatus::Requested, InspectionStatus::Confirmed, InspectionStatus::Rescheduled])->count()
                        + (clone $applications)->whereIn('status', [RentalApplicationStatus::Submitted, RentalApplicationStatus::UnderReview])->count()
                        + (clone $maintenance)->whereIn('status', [MaintenanceStatus::Open, MaintenanceStatus::Assigned, MaintenanceStatus::InProgress, MaintenanceStatus::AwaitingConfirmation])->count()
                    ),
                    'detail' => 'Combined inspection, application, and maintenance queues needing attention.',
                ],
                [
                    'label' => 'Outstanding Balance',
                    'value' => $this->currency((clone $invoices)->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])->sum('balance_amount')),
                    'detail' => $this->number((clone $payments)->where('status', PaymentStatus::PendingVerification)->count()).' payments are awaiting review.',
                ],
            ],
            Role::Landlord => [
                [
                    'label' => 'Portfolio Properties',
                    'value' => $this->number((clone $properties)->count()),
                    'detail' => $this->number((clone $properties)->where('publish_status', PropertyPublishStatus::Published)->count()).' are currently published.',
                ],
                [
                    'label' => 'Occupied Units',
                    'value' => $this->number((clone $units)->where('occupancy_status', UnitOccupancyStatus::Occupied)->count()),
                    'detail' => $this->number((clone $units)->count()).' total units under your portfolio.',
                ],
                [
                    'label' => 'Open Applications',
                    'value' => $this->number((clone $applications)->whereIn('status', [RentalApplicationStatus::Submitted, RentalApplicationStatus::UnderReview])->count()),
                    'detail' => $this->number((clone $inspections)->whereIn('status', [InspectionStatus::Requested, InspectionStatus::Confirmed, InspectionStatus::Rescheduled])->count()).' inspection requests are still active.',
                ],
                [
                    'label' => 'Outstanding Balance',
                    'value' => $this->currency((clone $invoices)->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])->sum('balance_amount')),
                    'detail' => $this->number((clone $maintenance)->whereIn('status', [MaintenanceStatus::Open, MaintenanceStatus::Assigned, MaintenanceStatus::InProgress])->count()).' unresolved maintenance issues across owned properties.',
                ],
            ],
            Role::Caretaker => [
                [
                    'label' => 'Assigned Properties',
                    'value' => $this->number((clone $properties)->count()),
                    'detail' => $this->number((clone $units)->count()).' units are within your operating scope.',
                ],
                [
                    'label' => 'Today\'s Inspections',
                    'value' => $this->number((clone $inspections)->whereDate('requested_for_date', today())->count()),
                    'detail' => $this->number((clone $inspections)->whereIn('status', [InspectionStatus::Requested, InspectionStatus::Confirmed, InspectionStatus::Rescheduled])->count()).' still require coordination.',
                ],
                [
                    'label' => 'Open Maintenance',
                    'value' => $this->number((clone $maintenance)->whereIn('status', [MaintenanceStatus::Open, MaintenanceStatus::Assigned, MaintenanceStatus::InProgress, MaintenanceStatus::AwaitingConfirmation])->count()),
                    'detail' => $this->number((clone $maintenance)->where('status', MaintenanceStatus::Resolved)->count()).' resolved items are waiting to close out.',
                ],
                [
                    'label' => 'Occupied Units',
                    'value' => $this->number((clone $units)->where('occupancy_status', UnitOccupancyStatus::Occupied)->count()),
                    'detail' => $this->number((clone $tenancies)->whereIn('status', [TenancyStatus::Active, TenancyStatus::RenewalPending, TenancyStatus::Ending])->count()).' active resident records are tied to your properties.',
                ],
            ],
            Role::Tenant => [
                [
                    'label' => 'Active Tenancy',
                    'value' => $this->number((clone $tenancies)->whereIn('status', [TenancyStatus::PendingActivation, TenancyStatus::Active, TenancyStatus::RenewalPending, TenancyStatus::Ending])->count()),
                    'detail' => $this->number((clone $applications)->whereIn('status', [RentalApplicationStatus::Submitted, RentalApplicationStatus::UnderReview])->count()).' rental applications are still in progress.',
                ],
                [
                    'label' => 'Outstanding Balance',
                    'value' => $this->currency((clone $invoices)->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])->sum('balance_amount')),
                    'detail' => $this->number((clone $invoices)->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue])->count()).' invoices still need payment.',
                ],
                [
                    'label' => 'Pending Payments',
                    'value' => $this->number((clone $payments)->where('status', PaymentStatus::PendingVerification)->count()),
                    'detail' => $this->number((clone $payments)->where('status', PaymentStatus::Verified)->count()).' payments have already been verified.',
                ],
                [
                    'label' => 'Open Support Requests',
                    'value' => $this->number((clone $maintenance)->whereIn('status', [MaintenanceStatus::Open, MaintenanceStatus::Assigned, MaintenanceStatus::InProgress, MaintenanceStatus::AwaitingConfirmation])->count()),
                    'detail' => $this->number((clone $maintenance)->whereIn('status', [MaintenanceStatus::Resolved, MaintenanceStatus::Closed])->count()).' requests have been resolved or closed.',
                ],
            ],
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function commandCenterFor(User $user, Role $role): array
    {
        return match ($role) {
            Role::Admin, Role::Landlord => [
                $this->applicationQueue($user, $role),
                $this->paymentQueue($user, $role),
                $this->invoiceQueue($user),
                $this->maintenanceQueue($user),
                $this->leaseExpiryQueue($user),
            ],
            Role::Caretaker => [
                $this->inspectionQueue($user),
                $this->maintenanceQueue($user),
                $this->leaseExpiryQueue($user),
            ],
            Role::Tenant => [
                $this->invoiceQueue($user),
                $this->paymentQueue($user, $role),
                $this->maintenanceQueue($user),
                $this->leaseExpiryQueue($user),
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function applicationQueue(User $user, Role $role): array
    {
        $query = $this->applicationsQuery($user, $role)
            ->whereIn('status', [RentalApplicationStatus::Submitted, RentalApplicationStatus::UnderReview]);

        return [
            'title' => 'Applications Awaiting Review',
            'count' => $this->number((clone $query)->count()),
            'summary' => 'Fresh applications that need a decision or next review note.',
            'href' => $this->path('applications.index'),
            'cta' => 'Open applications',
            'empty' => 'No applications are waiting for review.',
            'items' => (clone $query)
                ->with(['property', 'unit'])
                ->latest('submitted_at')
                ->limit(3)
                ->get()
                ->map(fn (RentalApplication $application): array => [
                    'label' => $application->applicant_name,
                    'meta' => ($application->property?->title ?? 'Property').' / '.($application->unit?->unit_name ?? 'Unit pending'),
                    'badge' => $application->status->label(),
                    'badge_classes' => $application->status->badgeClasses(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function paymentQueue(User $user, Role $role): array
    {
        $query = $this->paymentsQuery($user, $role)
            ->where('status', PaymentStatus::PendingVerification);

        return [
            'title' => $role === Role::Tenant ? 'Payments Under Review' : 'Payment Proofs To Verify',
            'count' => $this->number((clone $query)->count()),
            'summary' => $role === Role::Tenant ? 'Transfers you submitted that are waiting for confirmation.' : 'Submitted payment proofs waiting for finance review.',
            'href' => $this->path('billing.index'),
            'cta' => 'Open billing',
            'empty' => 'No payment proofs are pending.',
            'items' => (clone $query)
                ->with(['tenantUser', 'invoice'])
                ->latest('paid_at')
                ->limit(3)
                ->get()
                ->map(fn (Payment $payment): array => [
                    'label' => $payment->payment_reference,
                    'meta' => ($payment->tenantUser?->name ?? 'Tenant').' paid '.$this->currency($payment->amount),
                    'badge' => $payment->status->label(),
                    'badge_classes' => $payment->status->badgeClasses(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function invoiceQueue(User $user): array
    {
        $query = $this->invoicesQuery($user)
            ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])
            ->where(function (Builder $inner) {
                $inner
                    ->where('status', InvoiceStatus::Overdue)
                    ->orWhereDate('due_date', '<=', today()->addDays(7));
            });

        return [
            'title' => 'Rent Needing Attention',
            'count' => $this->number((clone $query)->count()),
            'summary' => 'Open invoices that are overdue or due within seven days.',
            'href' => $this->path('billing.index'),
            'cta' => 'Open billing',
            'empty' => 'No urgent rent invoices right now.',
            'items' => (clone $query)
                ->with(['tenancy.property', 'tenantUser'])
                ->orderBy('due_date')
                ->limit(3)
                ->get()
                ->map(fn (Invoice $invoice): array => [
                    'label' => $invoice->invoice_number,
                    'meta' => $this->currency($invoice->balance_amount).' due '.$invoice->due_date?->format('M j, Y'),
                    'badge' => $invoice->status->label(),
                    'badge_classes' => $invoice->status->badgeClasses(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function maintenanceQueue(User $user): array
    {
        $query = $this->maintenanceQuery($user)
            ->whereIn('status', [MaintenanceStatus::Open, MaintenanceStatus::Assigned, MaintenanceStatus::InProgress, MaintenanceStatus::AwaitingConfirmation]);

        return [
            'title' => 'Open Support Work',
            'count' => $this->number((clone $query)->count()),
            'summary' => 'Maintenance requests that still need action or resident confirmation.',
            'href' => $this->path('maintenance.index'),
            'cta' => 'Open support',
            'empty' => 'No open support work.',
            'items' => (clone $query)
                ->with(['property', 'unit'])
                ->latest('reported_at')
                ->limit(3)
                ->get()
                ->map(fn (MaintenanceRequest $request): array => [
                    'label' => $request->title,
                    'meta' => ($request->property?->title ?? 'Property').' / '.($request->unit?->unit_name ?? 'Unit'),
                    'badge' => $request->status->label(),
                    'badge_classes' => $request->status->badgeClasses(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function leaseExpiryQueue(User $user): array
    {
        $query = $this->tenanciesQuery($user)
            ->whereIn('status', [TenancyStatus::Active, TenancyStatus::RenewalPending, TenancyStatus::Ending])
            ->whereNotNull('lease_end_date')
            ->whereDate('lease_end_date', '>=', today())
            ->whereDate('lease_end_date', '<=', today()->addDays(60));

        return [
            'title' => 'Lease Expiry Watch',
            'count' => $this->number((clone $query)->count()),
            'summary' => 'Active leases ending within the next sixty days.',
            'href' => $this->path('tenancies.index'),
            'cta' => 'Open tenancies',
            'empty' => 'No leases are expiring soon.',
            'items' => (clone $query)
                ->with(['property', 'unit', 'tenantUser'])
                ->orderBy('lease_end_date')
                ->limit(3)
                ->get()
                ->map(fn (Tenancy $tenancy): array => [
                    'label' => $tenancy->tenant_name,
                    'meta' => ($tenancy->property?->title ?? 'Property').' ends '.$tenancy->lease_end_date?->format('M j, Y'),
                    'badge' => $tenancy->status->label(),
                    'badge_classes' => $tenancy->status->badgeClasses(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function inspectionQueue(User $user): array
    {
        $query = $this->inspectionsQuery($user)
            ->whereIn('status', [InspectionStatus::Requested, InspectionStatus::Confirmed, InspectionStatus::Rescheduled]);

        return [
            'title' => 'Inspection Run Sheet',
            'count' => $this->number((clone $query)->count()),
            'summary' => 'Viewing requests and scheduled inspections still on the operations desk.',
            'href' => $this->path('inspections.index'),
            'cta' => 'Open inspections',
            'empty' => 'No active inspections right now.',
            'items' => (clone $query)
                ->with(['property', 'unit'])
                ->orderBy('requested_for_date')
                ->limit(3)
                ->get()
                ->map(fn (Inspection $inspection): array => [
                    'label' => $inspection->requester_name,
                    'meta' => ($inspection->property?->title ?? 'Property').' on '.$inspection->requested_for_date?->format('M j, Y'),
                    'badge' => $inspection->status->label(),
                    'badge_classes' => $inspection->status->badgeClasses(),
                ])
                ->all(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function reportGroupsFor(User $user, Role $role): array
    {
        $properties = $this->propertiesQuery($user);
        $units = $this->unitsQuery($user);
        $inspections = $this->inspectionsQuery($user);
        $applications = $this->applicationsQuery($user, $role);
        $tenancies = $this->tenanciesQuery($user);
        $invoices = $this->invoicesQuery($user);
        $payments = $this->paymentsQuery($user, $role);
        $maintenance = $this->maintenanceQuery($user);

        $activeTenancy = $role === Role::Tenant
            ? (clone $tenancies)->whereIn('status', [TenancyStatus::PendingActivation, TenancyStatus::Active, TenancyStatus::RenewalPending, TenancyStatus::Ending])->with(['property', 'unit'])->latest('lease_start_date')->first()
            : null;

        return match ($role) {
            Role::Admin => [
                [
                    'title' => 'Operations Overview',
                    'summary' => 'Cross-module health for supply, leasing, and resident support.',
                    'metrics' => [
                        ['label' => 'Total Properties', 'value' => $this->number((clone $properties)->count()), 'detail' => 'All property records in the platform.'],
                        ['label' => 'Published Listings', 'value' => $this->number((clone $properties)->where('publish_status', PropertyPublishStatus::Published)->count()), 'detail' => 'Properties visible to the public marketplace.'],
                        ['label' => 'Requested Inspections', 'value' => $this->number((clone $inspections)->where('status', InspectionStatus::Requested)->count()), 'detail' => 'Fresh viewing requests waiting for action.'],
                        ['label' => 'Open Maintenance', 'value' => $this->number((clone $maintenance)->whereIn('status', [MaintenanceStatus::Open, MaintenanceStatus::Assigned, MaintenanceStatus::InProgress])->count()), 'detail' => 'Maintenance issues that are not yet resolved.'],
                    ],
                ],
                [
                    'title' => 'Revenue and Occupancy',
                    'summary' => 'Collections pressure and tenancy coverage across live inventory.',
                    'metrics' => [
                        ['label' => 'Active Tenancies', 'value' => $this->number((clone $tenancies)->whereIn('status', [TenancyStatus::Active, TenancyStatus::RenewalPending, TenancyStatus::Ending])->count()), 'detail' => 'Residents currently occupying or nearing renewal.'],
                        ['label' => 'Occupied Units', 'value' => $this->number((clone $units)->where('occupancy_status', UnitOccupancyStatus::Occupied)->count()), 'detail' => 'Units marked as occupied right now.'],
                        ['label' => 'Pending Payments', 'value' => $this->number((clone $payments)->where('status', PaymentStatus::PendingVerification)->count()), 'detail' => 'Transfers that still need verification.'],
                        ['label' => 'Outstanding Invoices', 'value' => $this->currency((clone $invoices)->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])->sum('balance_amount')), 'detail' => 'Unpaid balances still sitting on invoices.'],
                    ],
                ],
            ],
            Role::Landlord => [
                [
                    'title' => 'Portfolio Health',
                    'summary' => 'Supply, occupancy, and listing readiness for your assets.',
                    'metrics' => [
                        ['label' => 'Published Properties', 'value' => $this->number((clone $properties)->where('publish_status', PropertyPublishStatus::Published)->count()), 'detail' => 'Properties currently live on the marketplace.'],
                        ['label' => 'Vacant Units', 'value' => $this->number((clone $units)->where('occupancy_status', UnitOccupancyStatus::Vacant)->count()), 'detail' => 'Units immediately available to convert.'],
                        ['label' => 'Reserved Units', 'value' => $this->number((clone $units)->where('occupancy_status', UnitOccupancyStatus::Reserved)->count()), 'detail' => 'Units being held while deals progress.'],
                        ['label' => 'Occupied Units', 'value' => $this->number((clone $units)->where('occupancy_status', UnitOccupancyStatus::Occupied)->count()), 'detail' => 'Current occupied stock in your portfolio.'],
                    ],
                ],
                [
                    'title' => 'Leasing and Collections',
                    'summary' => 'Monitor deal flow and cash exposure without leaving the dashboard.',
                    'metrics' => [
                        ['label' => 'Submitted Applications', 'value' => $this->number((clone $applications)->where('status', RentalApplicationStatus::Submitted)->count()), 'detail' => 'Applications newly waiting for review.'],
                        ['label' => 'Approved Applications', 'value' => $this->number((clone $applications)->whereIn('status', [RentalApplicationStatus::Approved, RentalApplicationStatus::Converted])->count()), 'detail' => 'Applications already cleared or converted.'],
                        ['label' => 'Pending Payments', 'value' => $this->number((clone $payments)->where('status', PaymentStatus::PendingVerification)->count()), 'detail' => 'Payment proofs still waiting for approval.'],
                        ['label' => 'Outstanding Balance', 'value' => $this->currency((clone $invoices)->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])->sum('balance_amount')), 'detail' => 'Remaining unpaid value across tenant invoices.'],
                    ],
                ],
            ],
            Role::Caretaker => [
                [
                    'title' => 'Site Operations',
                    'summary' => 'Visibility into inspections, occupied homes, and unit readiness.',
                    'metrics' => [
                        ['label' => 'Listed Units', 'value' => $this->number((clone $units)->where('is_listed', true)->count()), 'detail' => 'Units currently prepared for listing visibility.'],
                        ['label' => 'Vacant Units', 'value' => $this->number((clone $units)->where('occupancy_status', UnitOccupancyStatus::Vacant)->count()), 'detail' => 'Units available for handover or inspection.'],
                        ['label' => 'Occupied Units', 'value' => $this->number((clone $units)->where('occupancy_status', UnitOccupancyStatus::Occupied)->count()), 'detail' => 'Residents currently living in your scope.'],
                        ['label' => 'Under Maintenance', 'value' => $this->number((clone $units)->where('occupancy_status', UnitOccupancyStatus::UnderMaintenance)->count()), 'detail' => 'Units blocked by maintenance readiness.'],
                    ],
                ],
                [
                    'title' => 'Service Queue',
                    'summary' => 'What needs hands-on follow-up from the operations side.',
                    'metrics' => [
                        ['label' => 'Requested Inspections', 'value' => $this->number((clone $inspections)->where('status', InspectionStatus::Requested)->count()), 'detail' => 'New viewing requests still waiting for confirmation.'],
                        ['label' => 'Confirmed Inspections', 'value' => $this->number((clone $inspections)->where('status', InspectionStatus::Confirmed)->count()), 'detail' => 'Visits already scheduled and upcoming.'],
                        ['label' => 'Assigned Maintenance', 'value' => $this->number((clone $maintenance)->whereIn('status', [MaintenanceStatus::Assigned, MaintenanceStatus::InProgress])->count()), 'detail' => 'Requests actively being worked on.'],
                        ['label' => 'Resolved Today', 'value' => $this->number((clone $maintenance)->whereDate('resolved_at', today())->count()), 'detail' => 'Issues marked resolved during today\'s shift.'],
                    ],
                ],
            ],
            Role::Tenant => [
                [
                    'title' => 'Home Snapshot',
                    'summary' => 'Your current tenancy and move-related status at a glance.',
                    'metrics' => [
                        ['label' => 'Current Property', 'value' => $activeTenancy?->property?->title ?? 'Not assigned yet', 'detail' => 'Property connected to your active tenancy.'],
                        ['label' => 'Current Unit', 'value' => $activeTenancy?->unit?->unit_name ?? 'Pending allocation', 'detail' => 'Unit currently attached to your tenancy record.'],
                        ['label' => 'Tenancy Status', 'value' => $activeTenancy?->status?->label() ?? 'No active tenancy', 'detail' => 'Latest lease lifecycle state on your account.'],
                        ['label' => 'Move-in Date', 'value' => $activeTenancy?->move_in_date?->format('M j, Y') ?? 'Not scheduled', 'detail' => 'Recorded move-in date for your current lease.'],
                    ],
                ],
                [
                    'title' => 'Billing and Support',
                    'summary' => 'Outstanding balances, payment progress, and support work in one place.',
                    'metrics' => [
                        ['label' => 'Outstanding Invoices', 'value' => $this->number((clone $invoices)->whereIn('status', [InvoiceStatus::Issued, InvoiceStatus::PartiallyPaid, InvoiceStatus::Overdue])->count()), 'detail' => 'Bills still waiting for full settlement.'],
                        ['label' => 'Outstanding Balance', 'value' => $this->currency((clone $invoices)->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])->sum('balance_amount')), 'detail' => 'Unpaid amount still on your account.'],
                        ['label' => 'Verified Payments', 'value' => $this->number((clone $payments)->where('status', PaymentStatus::Verified)->count()), 'detail' => 'Payments that have already cleared verification.'],
                        ['label' => 'Open Support Requests', 'value' => $this->number((clone $maintenance)->whereIn('status', [MaintenanceStatus::Open, MaintenanceStatus::Assigned, MaintenanceStatus::InProgress, MaintenanceStatus::AwaitingConfirmation])->count()), 'detail' => 'Maintenance issues still being worked on.'],
                    ],
                ],
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    protected function focusItemsFor(User $user, Role $role): array
    {
        $invoices = $this->invoicesQuery($user);
        $payments = $this->paymentsQuery($user, $role);
        $inspections = $this->inspectionsQuery($user);
        $applications = $this->applicationsQuery($user, $role);
        $maintenance = $this->maintenanceQuery($user);

        return match ($role) {
            Role::Admin => [
                ['title' => 'Users & Access', 'description' => $this->number(User::query()->count()).' total user accounts are registered on the platform.', 'href' => $this->path('admin.users.index'), 'cta' => 'Open users'],
                ['title' => 'Payment Reviews', 'description' => $this->number((clone $payments)->where('status', PaymentStatus::PendingVerification)->count()).' submitted payments still need verification.', 'href' => $this->path('billing.index'), 'cta' => 'Review billing'],
                ['title' => 'Support Queue', 'description' => $this->number((clone $maintenance)->whereIn('status', [MaintenanceStatus::Open, MaintenanceStatus::Assigned, MaintenanceStatus::InProgress])->count()).' maintenance items are unresolved.', 'href' => $this->path('maintenance.index'), 'cta' => 'View maintenance'],
            ],
            Role::Landlord => [
                ['title' => 'Leasing Pipeline', 'description' => $this->number((clone $applications)->whereIn('status', [RentalApplicationStatus::Submitted, RentalApplicationStatus::UnderReview])->count()).' applications are moving through review.', 'href' => $this->path('applications.index'), 'cta' => 'Review applications'],
                ['title' => 'Collections', 'description' => $this->currency((clone $invoices)->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])->sum('balance_amount')).' remains outstanding.', 'href' => $this->path('billing.index'), 'cta' => 'Open billing'],
                ['title' => 'Service Delivery', 'description' => $this->number((clone $maintenance)->whereIn('status', [MaintenanceStatus::Open, MaintenanceStatus::Assigned, MaintenanceStatus::InProgress])->count()).' maintenance items and '.$this->number((clone $inspections)->whereIn('status', [InspectionStatus::Requested, InspectionStatus::Confirmed, InspectionStatus::Rescheduled])->count()).' live inspections need attention.', 'href' => $this->path('maintenance.index'), 'cta' => 'Check operations'],
            ],
            Role::Caretaker => [
                ['title' => 'Inspection Desk', 'description' => $this->number((clone $inspections)->whereIn('status', [InspectionStatus::Requested, InspectionStatus::Confirmed, InspectionStatus::Rescheduled])->count()).' inspections are active in your queue.', 'href' => $this->path('inspections.index'), 'cta' => 'Open inspections'],
                ['title' => 'Maintenance Run Sheet', 'description' => $this->number((clone $maintenance)->whereIn('status', [MaintenanceStatus::Assigned, MaintenanceStatus::InProgress])->count()).' requests are assigned or in progress.', 'href' => $this->path('maintenance.index'), 'cta' => 'Open maintenance'],
                ['title' => 'Unit Readiness', 'description' => $this->number($this->unitsQuery($user)->where('occupancy_status', UnitOccupancyStatus::Vacant)->count()).' units are vacant and '.$this->number($this->unitsQuery($user)->where('occupancy_status', UnitOccupancyStatus::UnderMaintenance)->count()).' are blocked by repairs.', 'href' => $this->path('properties.index'), 'cta' => 'View properties'],
            ],
            Role::Tenant => [
                ['title' => 'Rent Status', 'description' => $this->currency((clone $invoices)->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])->sum('balance_amount')).' is still outstanding on your account.', 'href' => $this->path('billing.index'), 'cta' => 'View billing'],
                ['title' => 'Support Progress', 'description' => $this->number((clone $maintenance)->whereIn('status', [MaintenanceStatus::Open, MaintenanceStatus::Assigned, MaintenanceStatus::InProgress, MaintenanceStatus::AwaitingConfirmation])->count()).' maintenance requests are still open.', 'href' => $this->path('maintenance.index'), 'cta' => 'Open support'],
                ['title' => 'Payment Reviews', 'description' => $this->number((clone $payments)->where('status', PaymentStatus::PendingVerification)->count()).' submitted payments are waiting to be verified.', 'href' => $this->path('billing.index'), 'cta' => 'Track payments'],
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    protected function recentActivityFor(User $user, Role $role): array
    {
        $items = collect();

        foreach ($this->inspectionsQuery($user)->with(['property', 'unit'])->latest()->take(2)->get() as $inspection) {
            $items->push([
                'title' => 'Inspection '.$inspection->status->label(),
                'meta' => trim(($inspection->property?->title ?? 'Property').' '.($inspection->unit?->unit_name ? ' / '.$inspection->unit->unit_name : '')),
                'timestamp' => $inspection->created_at,
                'time' => $inspection->created_at?->diffForHumans() ?? 'just now',
                'href' => $this->path('inspections.index'),
            ]);
        }

        foreach ($this->maintenanceQuery($user)->with(['property', 'unit'])->latest()->take(2)->get() as $request) {
            $items->push([
                'title' => 'Maintenance '.$request->status->label(),
                'meta' => $request->title.' at '.($request->property?->title ?? 'property'),
                'timestamp' => $request->created_at,
                'time' => $request->created_at?->diffForHumans() ?? 'just now',
                'href' => $this->path('maintenance.index'),
            ]);
        }

        foreach ($this->invoicesQuery($user)->with('tenancy.property')->latest()->take(2)->get() as $invoice) {
            $items->push([
                'title' => 'Invoice '.$invoice->status->label(),
                'meta' => $invoice->invoice_number.' for '.($invoice->tenancy?->property?->title ?? 'tenancy').'. Balance '.$this->currency($invoice->balance_amount),
                'timestamp' => $invoice->created_at,
                'time' => $invoice->created_at?->diffForHumans() ?? 'just now',
                'href' => $this->path('billing.index'),
            ]);
        }

        if (in_array($role, [Role::Admin, Role::Landlord, Role::Tenant], true)) {
            foreach ($this->applicationsQuery($user, $role)->with(['property', 'unit'])->latest()->take(2)->get() as $application) {
                $items->push([
                    'title' => 'Application '.$application->status->label(),
                    'meta' => $application->applicant_name.' for '.($application->property?->title ?? 'property'),
                    'timestamp' => $application->created_at,
                    'time' => $application->created_at?->diffForHumans() ?? 'just now',
                    'href' => $role === Role::Tenant ? $this->path('tenancies.index') : $this->path('applications.index'),
                ]);
            }
        }

        if ($role === Role::Tenant) {
            foreach ($this->tenanciesQuery($user)->with(['property', 'unit'])->latest()->take(1)->get() as $tenancy) {
                $items->push([
                    'title' => 'Tenancy '.$tenancy->status->label(),
                    'meta' => ($tenancy->property?->title ?? 'Property').' / '.($tenancy->unit?->unit_name ?? 'Unit'),
                    'timestamp' => $tenancy->created_at,
                    'time' => $tenancy->created_at?->diffForHumans() ?? 'just now',
                    'href' => $this->path('tenancies.index'),
                ]);
            }
        }

        /** @var Collection<int, array<string, mixed>> $activity */
        $activity = $items
            ->sortByDesc('timestamp')
            ->take(6)
            ->values()
            ->map(fn (array $item): array => [
                'title' => (string) $item['title'],
                'meta' => (string) $item['meta'],
                'time' => (string) $item['time'],
                'href' => (string) $item['href'],
            ]);

        return $activity->all();
    }

    protected function propertiesQuery(User $user): Builder
    {
        return Property::query()->visibleTo($user);
    }

    protected function unitsQuery(User $user): Builder
    {
        if ($user->hasRole(Role::Tenant->value)) {
            return PropertyUnit::query()->whereHas('tenancies', fn (Builder $tenancyQuery) => $tenancyQuery->where('tenant_user_id', $user->getKey()));
        }

        return PropertyUnit::query()->whereHas('property', fn (Builder $propertyQuery) => $propertyQuery->visibleTo($user));
    }

    protected function inspectionsQuery(User $user): Builder
    {
        return Inspection::query()->visibleTo($user);
    }

    protected function applicationsQuery(User $user, Role $role): Builder
    {
        if ($role === Role::Tenant) {
            return RentalApplication::query()->where('applicant_user_id', $user->getKey());
        }

        return RentalApplication::query()->visibleTo($user);
    }

    protected function tenanciesQuery(User $user): Builder
    {
        return Tenancy::query()->visibleTo($user);
    }

    protected function invoicesQuery(User $user): Builder
    {
        return Invoice::query()->visibleTo($user);
    }

    protected function paymentsQuery(User $user, Role $role): Builder
    {
        if ($role === Role::Caretaker) {
            return Payment::query()->whereRaw('1 = 0');
        }

        return Payment::query()->visibleTo($user);
    }

    protected function maintenanceQuery(User $user): Builder
    {
        return MaintenanceRequest::query()->visibleTo($user);
    }

    protected function number(int|float|string|null $value): string
    {
        return number_format((float) $value, 0);
    }

    protected function currency(int|float|string|null $value): string
    {
        return 'NGN '.number_format((float) $value, 0);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function path(string $name, array $parameters = []): string
    {
        return route($name, $parameters, false);
    }
}
