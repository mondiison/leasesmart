<?php

namespace App\Enums;

enum RentalApplicationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Converted = 'converted';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Converted => 'Converted',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200',
            self::Submitted => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-400/10 dark:text-cyan-200',
            self::UnderReview => 'bg-amber-100 text-amber-800 dark:bg-amber-400/10 dark:text-amber-200',
            self::Approved => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200',
            self::Rejected => 'bg-rose-100 text-rose-800 dark:bg-rose-400/10 dark:text-rose-200',
            self::Converted => 'bg-violet-100 text-violet-800 dark:bg-violet-400/10 dark:text-violet-200',
        };
    }
}
