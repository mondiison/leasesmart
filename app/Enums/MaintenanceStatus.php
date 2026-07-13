<?php

namespace App\Enums;

enum MaintenanceStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case AwaitingConfirmation = 'awaiting_confirmation';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Assigned => 'Assigned',
            self::InProgress => 'In Progress',
            self::AwaitingConfirmation => 'Awaiting Confirmation',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Open => 'bg-amber-100 text-amber-800 dark:bg-amber-400/10 dark:text-amber-200',
            self::Assigned => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-400/10 dark:text-cyan-200',
            self::InProgress => 'bg-blue-100 text-blue-800 dark:bg-blue-400/10 dark:text-blue-200',
            self::AwaitingConfirmation => 'bg-violet-100 text-violet-800 dark:bg-violet-400/10 dark:text-violet-200',
            self::Resolved => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200',
            self::Closed => 'bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200',
            self::Cancelled => 'bg-rose-100 text-rose-800 dark:bg-rose-400/10 dark:text-rose-200',
        };
    }
}
