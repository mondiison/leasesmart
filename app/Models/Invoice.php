<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'tenant_user_id',
        'invoice_number',
        'invoice_type',
        'issue_date',
        'due_date',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'balance_amount',
        'status',
        'notes',
        'issued_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_type' => InvoiceType::class,
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'status' => InvoiceStatus::class,
        ];
    }

    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function tenantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payment_allocations')->withPivot('amount')->withTimestamps();
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable')->latest();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('landlord') && $user->landlordProfile) {
            return $query->whereHas('tenancy.property', fn (Builder $propertyQuery) => $propertyQuery->where('landlord_id', $user->landlordProfile->getKey()));
        }

        if ($user->hasRole('tenant')) {
            return $query->where('tenant_user_id', $user->getKey());
        }

        return $query->whereRaw('1 = 0');
    }

    public function refreshStatusFromBalance(): void
    {
        $balance = (float) $this->balance_amount;

        $status = match (true) {
            $balance <= 0 => InvoiceStatus::Paid,
            $balance < (float) $this->total_amount => InvoiceStatus::PartiallyPaid,
            $this->due_date->isPast() => InvoiceStatus::Overdue,
            default => InvoiceStatus::Issued,
        };

        $this->forceFill(['status' => $status])->save();
    }
}
