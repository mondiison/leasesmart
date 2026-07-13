<?php

namespace App\Actions\Reports;

use App\Actions\Activity\LogActivityAction;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\WeeklyReportDigestNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class SendWeeklyReportDigestsAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    public function execute(?User $actor = null, ?CarbonImmutable $reportDate = null, string $role = 'all', bool $force = false): int
    {
        $reportDate ??= CarbonImmutable::today();
        $weekStart = $reportDate->startOfWeek()->toDateString();
        $sent = 0;

        foreach ($this->recipients($role) as $recipient) {
            if (! $force && $this->alreadySent($recipient, $weekStart)) {
                continue;
            }

            $recipient->notify(new WeeklyReportDigestNotification($recipient, $reportDate));

            $this->logActivity->execute(
                user: $actor,
                action: 'weekly_report_digest_sent',
                description: "Weekly report digest sent to {$recipient->name}.",
                subject: $recipient,
                metadata: [
                    'recipient_role' => $recipient->roleLabel(),
                    'week_start' => $weekStart,
                    'report_date' => $reportDate->toDateString(),
                ],
            );

            $sent++;
        }

        return $sent;
    }

    /**
     * @return Collection<int, User>
     */
    protected function recipients(string $role): Collection
    {
        $query = User::query()
            ->where('is_active', true)
            ->whereNotNull('email_verified_at')
            ->whereHas('roles', fn ($roles) => $roles->whereIn('name', ['admin', 'landlord']))
            ->orderBy('id');

        if (in_array($role, ['admin', 'landlord'], true)) {
            $query->role($role);
        }

        return $query->get();
    }

    protected function alreadySent(User $recipient, string $weekStart): bool
    {
        return ActivityLog::query()
            ->where('loggable_type', $recipient->getMorphClass())
            ->where('loggable_id', $recipient->getKey())
            ->where('action', 'weekly_report_digest_sent')
            ->where('metadata->week_start', $weekStart)
            ->exists();
    }
}
