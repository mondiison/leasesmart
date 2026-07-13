<?php

namespace App\Actions\Billing;

use App\Enums\BillingCycle;
use App\Enums\InvoiceType;
use App\Enums\TenancyStatus;
use App\Models\Invoice;
use App\Models\Tenancy;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class GenerateRecurringRentInvoicesAction
{
    public function __construct(protected CreateInvoiceAction $createInvoice)
    {
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function execute(User $actor, ?CarbonImmutable $billingDate = null): Collection
    {
        $billingDate ??= CarbonImmutable::today();
        $created = collect();

        Tenancy::query()
            ->with(['property', 'tenantUser'])
            ->whereIn('status', [
                TenancyStatus::Active,
                TenancyStatus::RenewalPending,
                TenancyStatus::Ending,
            ])
            ->whereDate('lease_start_date', '<=', $billingDate->toDateString())
            ->where(function ($query) use ($billingDate): void {
                $query
                    ->whereNull('lease_end_date')
                    ->orWhereDate('lease_end_date', '>=', $billingDate->toDateString());
            })
            ->orderBy('id')
            ->each(function (Tenancy $tenancy) use ($actor, $billingDate, $created): void {
                if ((float) $tenancy->rent_amount <= 0) {
                    return;
                }

                $periodStart = $this->periodStartFor($tenancy, $billingDate);

                if ($periodStart === null) {
                    return;
                }

                $alreadyIssued = Invoice::query()
                    ->where('tenancy_id', $tenancy->getKey())
                    ->where('invoice_type', InvoiceType::Rent)
                    ->whereDate('issue_date', $periodStart->toDateString())
                    ->exists();

                if ($alreadyIssued) {
                    return;
                }

                $created->push($this->createInvoice->execute($actor, $tenancy, [
                    'invoice_type' => InvoiceType::Rent->value,
                    'issue_date' => $periodStart->toDateString(),
                    'due_date' => $periodStart->addDays(7)->toDateString(),
                    'description' => 'Recurring rent for '.$periodStart->format('M j, Y').' billing period',
                    'notes' => 'Generated automatically by LeaseSmart recurring billing.',
                ]));
            });

        return $created;
    }

    protected function periodStartFor(Tenancy $tenancy, CarbonImmutable $billingDate): ?CarbonImmutable
    {
        $leaseStart = CarbonImmutable::parse($tenancy->lease_start_date)->startOfDay();

        if ($leaseStart->greaterThan($billingDate)) {
            return null;
        }

        $months = $this->cycleMonths($tenancy->billing_cycle);
        $periodStart = $leaseStart;

        while ($periodStart->addMonthsNoOverflow($months)->lessThanOrEqualTo($billingDate)) {
            $periodStart = $periodStart->addMonthsNoOverflow($months);
        }

        return $periodStart;
    }

    protected function cycleMonths(BillingCycle $cycle): int
    {
        return match ($cycle) {
            BillingCycle::Monthly => 1,
            BillingCycle::Quarterly => 3,
            BillingCycle::Biannual => 6,
            BillingCycle::Yearly => 12,
        };
    }
}
