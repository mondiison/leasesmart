<?php

namespace App\Enums;

enum PropertyPublishStatus: string
{
    case Draft = 'draft';
    case UnderReview = 'under_review';
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::UnderReview => 'Under Review',
            self::Published => 'Published',
            self::Unpublished => 'Unpublished',
            self::Archived => 'Archived',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200',
            self::UnderReview => 'bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-200',
            self::Published => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-200',
            self::Unpublished => 'bg-rose-100 text-rose-700 dark:bg-rose-400/10 dark:text-rose-200',
            self::Archived => 'bg-slate-200 text-slate-700 dark:bg-slate-400/10 dark:text-slate-200',
        };
    }
}
