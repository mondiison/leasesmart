<?php

namespace App\Support\Search;

use App\Enums\Role;
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

class GlobalSearch
{
    /**
     * @return list<array<string, mixed>>
     */
    public function for(User $user, string $term): array
    {
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return [];
        }

        $like = '%'.$term.'%';
        $role = $user->primaryRole() ?? Role::Tenant;

        return array_values(array_filter([
            $this->properties($user, $like),
            $this->units($user, $like),
            $this->tenancies($user, $like),
            $this->invoices($user, $like),
            $this->payments($user, $like),
            in_array($role, [Role::Admin, Role::Landlord, Role::Tenant], true) ? $this->applications($user, $role, $like) : null,
            ! $user->hasRole(Role::Tenant->value) ? $this->inspections($user, $like) : null,
            $this->maintenance($user, $like),
            $user->hasRole(Role::Admin->value) ? $this->users($like) : null,
        ], static fn ($group): bool => $group !== null && $group['items'] !== []));
    }

    /**
     * @return array<string, mixed>
     */
    protected function properties(User $user, string $like): array
    {
        return [
            'title' => 'Properties',
            'items' => Property::query()
                ->visibleTo($user)
                ->where(function (Builder $query) use ($like) {
                    $query
                        ->where('title', 'like', $like)
                        ->orWhere('property_code', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('state', 'like', $like)
                        ->orWhere('address_line_1', 'like', $like);
                })
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Property $property): array => [
                    'title' => $property->title,
                    'meta' => trim($property->property_code.' - '.$property->city.', '.$property->state, ' -,'),
                    'href' => $this->path('properties.index', ['q' => $property->title]),
                    'badge' => $property->publish_status->label(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function units(User $user, string $like): array
    {
        return [
            'title' => 'Units',
            'items' => PropertyUnit::query()
                ->with('property')
                ->whereHas('property', fn (Builder $query) => $query->visibleTo($user))
                ->where(function (Builder $query) use ($like) {
                    $query
                        ->where('unit_name', 'like', $like)
                        ->orWhere('unit_code', 'like', $like)
                        ->orWhere('unit_type', 'like', $like)
                        ->orWhere('description', 'like', $like);
                })
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (PropertyUnit $unit): array => [
                    'title' => $unit->unit_name,
                    'meta' => ($unit->property?->title ?? 'Property').' - '.$unit->unit_type,
                    'href' => $this->path('properties.index', ['q' => $unit->unit_name]),
                    'badge' => $unit->occupancy_status->label(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function tenancies(User $user, string $like): array
    {
        return [
            'title' => 'Tenancies',
            'items' => Tenancy::query()
                ->with(['property', 'unit'])
                ->visibleTo($user)
                ->where(function (Builder $query) use ($like) {
                    $query
                        ->where('tenant_name', 'like', $like)
                        ->orWhere('tenant_email', 'like', $like)
                        ->orWhere('tenant_phone', 'like', $like)
                        ->orWhereHas('property', fn (Builder $propertyQuery) => $propertyQuery->where('title', 'like', $like))
                        ->orWhereHas('unit', fn (Builder $unitQuery) => $unitQuery->where('unit_name', 'like', $like));
                })
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Tenancy $tenancy): array => [
                    'title' => $tenancy->tenant_name,
                    'meta' => ($tenancy->property?->title ?? 'Property').' / '.($tenancy->unit?->unit_name ?? 'Unit'),
                    'href' => $this->path('tenancies.index', ['q' => $tenancy->tenant_name]),
                    'badge' => $tenancy->status->label(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function invoices(User $user, string $like): array
    {
        return [
            'title' => 'Invoices',
            'items' => Invoice::query()
                ->with('tenancy.property')
                ->visibleTo($user)
                ->where(function (Builder $query) use ($like) {
                    $query
                        ->where('invoice_number', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhereHas('tenantUser', fn (Builder $tenantQuery) => $tenantQuery->where('name', 'like', $like)->orWhere('email', 'like', $like))
                        ->orWhereHas('tenancy.property', fn (Builder $propertyQuery) => $propertyQuery->where('title', 'like', $like));
                })
                ->latest('issue_date')
                ->limit(5)
                ->get()
                ->map(fn (Invoice $invoice): array => [
                    'title' => $invoice->invoice_number,
                    'meta' => ($invoice->tenancy?->property?->title ?? 'Property').' - NGN '.number_format((float) $invoice->balance_amount, 2),
                    'href' => $this->path('billing.index', ['q' => $invoice->invoice_number]),
                    'badge' => $invoice->status->label(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function payments(User $user, string $like): array
    {
        return [
            'title' => 'Payments',
            'items' => Payment::query()
                ->with('tenancy.property')
                ->visibleTo($user)
                ->where(function (Builder $query) use ($like) {
                    $query
                        ->where('payment_reference', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhereHas('tenantUser', fn (Builder $tenantQuery) => $tenantQuery->where('name', 'like', $like)->orWhere('email', 'like', $like))
                        ->orWhereHas('invoice', fn (Builder $invoiceQuery) => $invoiceQuery->where('invoice_number', 'like', $like));
                })
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Payment $payment): array => [
                    'title' => $payment->payment_reference,
                    'meta' => ($payment->tenancy?->property?->title ?? 'Property').' - NGN '.number_format((float) $payment->amount, 2),
                    'href' => $this->path('billing.index', ['q' => $payment->payment_reference]),
                    'badge' => $payment->status->label(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function applications(User $user, Role $role, string $like): array
    {
        $query = $role === Role::Tenant
            ? RentalApplication::query()->where('applicant_user_id', $user->getKey())
            : RentalApplication::query()->visibleTo($user);

        return [
            'title' => 'Applications',
            'items' => $query
                ->with(['property', 'unit'])
                ->where(function (Builder $query) use ($like) {
                    $query
                        ->where('applicant_name', 'like', $like)
                        ->orWhere('applicant_email', 'like', $like)
                        ->orWhere('applicant_phone', 'like', $like)
                        ->orWhereHas('property', fn (Builder $propertyQuery) => $propertyQuery->where('title', 'like', $like))
                        ->orWhereHas('unit', fn (Builder $unitQuery) => $unitQuery->where('unit_name', 'like', $like));
                })
                ->latest('submitted_at')
                ->limit(5)
                ->get()
                ->map(fn (RentalApplication $application): array => [
                    'title' => $application->applicant_name,
                    'meta' => ($application->property?->title ?? 'Property').' / '.($application->unit?->unit_name ?? 'Unit'),
                    'href' => $role === Role::Tenant ? $this->path('tenancies.index', ['q' => $application->property?->title]) : $this->path('applications.index', ['q' => $application->applicant_name]),
                    'badge' => $application->status->label(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function inspections(User $user, string $like): array
    {
        return [
            'title' => 'Inspections',
            'items' => Inspection::query()
                ->with(['property', 'unit'])
                ->visibleTo($user)
                ->where(function (Builder $query) use ($like) {
                    $query
                        ->where('requester_name', 'like', $like)
                        ->orWhere('requester_email', 'like', $like)
                        ->orWhere('requester_phone', 'like', $like)
                        ->orWhereHas('property', fn (Builder $propertyQuery) => $propertyQuery->where('title', 'like', $like))
                        ->orWhereHas('unit', fn (Builder $unitQuery) => $unitQuery->where('unit_name', 'like', $like));
                })
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Inspection $inspection): array => [
                    'title' => $inspection->requester_name,
                    'meta' => ($inspection->property?->title ?? 'Property').' / '.($inspection->unit?->unit_name ?? 'Unit'),
                    'href' => $this->path('inspections.index', ['q' => $inspection->requester_name]),
                    'badge' => $inspection->status->label(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function maintenance(User $user, string $like): array
    {
        return [
            'title' => 'Maintenance',
            'items' => MaintenanceRequest::query()
                ->with(['property', 'unit'])
                ->visibleTo($user)
                ->where(function (Builder $query) use ($like) {
                    $query
                        ->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('category', 'like', $like)
                        ->orWhereHas('property', fn (Builder $propertyQuery) => $propertyQuery->where('title', 'like', $like))
                        ->orWhereHas('unit', fn (Builder $unitQuery) => $unitQuery->where('unit_name', 'like', $like));
                })
                ->latest('reported_at')
                ->limit(5)
                ->get()
                ->map(fn (MaintenanceRequest $request): array => [
                    'title' => $request->title,
                    'meta' => ($request->property?->title ?? 'Property').' / '.($request->unit?->unit_name ?? 'Unit'),
                    'href' => $this->path('maintenance.index', ['q' => $request->title]),
                    'badge' => $request->status->label(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function users(string $like): array
    {
        return [
            'title' => 'Users',
            'items' => User::query()
                ->where(function (Builder $query) use ($like) {
                    $query
                        ->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                })
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (User $user): array => [
                    'title' => $user->name,
                    'meta' => $user->email,
                    'href' => $this->path('admin.users.index', ['q' => $user->email]),
                    'badge' => $user->roleLabel(),
                ])
                ->all(),
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function path(string $name, array $parameters = []): string
    {
        return route($name, $parameters, false);
    }
}
