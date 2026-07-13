<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PendingVerification = 'pending_verification';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PendingVerification => 'Pending Verification',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
            self::Refunded => 'Refunded',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PendingVerification => 'bg-amber-100 text-amber-800 dark:bg-amber-400/10 dark:text-amber-200',
            self::Verified => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/10 dark:text-emerald-200',
            self::Rejected => 'bg-rose-100 text-rose-800 dark:bg-rose-400/10 dark:text-rose-200',
            self::Refunded => 'bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200',
        };
    }
}
