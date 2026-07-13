<?php

namespace App\Actions\Tenancies;

use App\Actions\Activity\LogActivityAction;
use App\Enums\TenancyStatus;
use App\Models\ActivityLog;
use App\Models\Tenancy;
use App\Models\User;
use App\Notifications\LeaseExpiryAlertNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class SendLeaseExpiryAlertsAction
{
    /**
     * @param list<int> $alertDays
     */
    public function __construct(
        protected LogActivityAction $logActivity,
        protected array $alertDays = [90, 60, 30, 7, 0],
    ) {
    }

    public function execute(?User $actor = null, ?CarbonImmutable $asOf = null): int
    {
        $asOf ??= CarbonImmutable::today();
        $sent = 0;

        Tenancy::query()
            ->with(['property.landlord.user', 'property.caretaker.user', 'tenantUser', 'unit'])
            ->whereIn('status', [
                TenancyStatus::Active,
                TenancyStatus::RenewalPending,
                TenancyStatus::Ending,
            ])
            ->whereNotNull('lease_end_date')
            ->whereDate('lease_end_date', '>=', $asOf->toDateString())
            ->whereDate('lease_end_date', '<=', $asOf->addDays(max($this->alertDays))->toDateString())
            ->orderBy('lease_end_date')
            ->each(function (Tenancy $tenancy) use ($actor, $asOf, &$sent): void {
                $daysRemaining = (int) $asOf->diffInDays(CarbonImmutable::parse($tenancy->lease_end_date), false);

                if (! in_array($daysRemaining, $this->alertDays, true) || $this->alreadySent($tenancy, $daysRemaining)) {
                    return;
                }

                $recipients = $this->recipientsFor($tenancy);

                if ($recipients->isEmpty()) {
                    return;
                }

                Notification::send($recipients, new LeaseExpiryAlertNotification($tenancy, $daysRemaining));

                $this->logActivity->execute(
                    user: $actor,
                    action: 'lease_expiry_alert_sent',
                    description: "Sent {$daysRemaining}-day lease expiry alert for tenancy #{$tenancy->getKey()}.",
                    subject: $tenancy,
                    metadata: [
                        'days_remaining' => $daysRemaining,
                        'lease_end_date' => $tenancy->lease_end_date?->toDateString(),
                        'recipient_ids' => $recipients->pluck('id')->all(),
                    ],
                );

                $sent++;
            });

        return $sent;
    }

    protected function alreadySent(Tenancy $tenancy, int $daysRemaining): bool
    {
        return ActivityLog::query()
            ->where('action', 'lease_expiry_alert_sent')
            ->where('loggable_type', $tenancy->getMorphClass())
            ->where('loggable_id', $tenancy->getKey())
            ->get()
            ->contains(fn (ActivityLog $log): bool => (int) ($log->metadata['days_remaining'] ?? -1) === $daysRemaining);
    }

    /**
     * @return Collection<int, User>
     */
    protected function recipientsFor(Tenancy $tenancy): Collection
    {
        return collect([
            $tenancy->tenantUser,
            $tenancy->property?->landlord?->user,
            $tenancy->property?->caretaker?->user,
        ])
            ->filter()
            ->unique('id')
            ->values();
    }
}
