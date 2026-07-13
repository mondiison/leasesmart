<?php

namespace App\Enums;

enum MaintenancePriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Low => 'bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200',
            self::Medium => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-400/10 dark:text-cyan-200',
            self::High => 'bg-amber-100 text-amber-800 dark:bg-amber-400/10 dark:text-amber-200',
            self::Urgent => 'bg-rose-100 text-rose-800 dark:bg-rose-400/10 dark:text-rose-200',
        };
    }
}
