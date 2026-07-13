<?php

namespace App\Enums;

enum TenancyStatus: string
{
    case PendingActivation = 'pending_activation';
    case Active = 'active';
    case RenewalPending = 'renewal_pending';
    case Ending = 'ending';
    case Ended = 'ended';

    public function label(): string
    {
        return match ($this) {
            self::PendingActivation => 'Pending Activation',
            self::Active => 'Active',
            self::RenewalPending => 'Renewal Pending',
            self::Ending => 'Ending',
            self::Ended => 'Ended',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PendingActivation => 'bg-amber-100 text-amber-800 dark:bg-amber-400/10 dark:text-amber-200',
            self::Active => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200',
            self::RenewalPending => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-400/10 dark:text-cyan-200',
            self::Ending => 'bg-violet-100 text-violet-800 dark:bg-violet-400/10 dark:text-violet-200',
            self::Ended => 'bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200',
        };
    }
}
