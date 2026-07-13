<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Issued => 'Issued',
            self::PartiallyPaid => 'Partially Paid',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200',
            self::Issued => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-400/10 dark:text-cyan-200',
            self::PartiallyPaid => 'bg-amber-100 text-amber-800 dark:bg-amber-400/10 dark:text-amber-200',
            self::Paid => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200',
            self::Overdue => 'bg-rose-100 text-rose-800 dark:bg-rose-400/10 dark:text-rose-200',
            self::Cancelled => 'bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200',
        };
    }
}
