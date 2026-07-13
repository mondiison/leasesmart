<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\TenancyStatus;
use App\Enums\UnitOccupancyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Tenancy extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'property_id',
        'property_unit_id',
        'rental_application_id',
        'tenant_user_id',
        'created_by',
        'updated_by',
        'status',
        'tenant_name',
        'tenant_email',
        'tenant_phone',
        'lease_start_date',
        'lease_end_date',
        'move_in_date',
        'activated_at',
        'ended_at',
        'rent_amount',
        'service_charge_amount',
        'billing_cycle',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => TenancyStatus::class,
            'lease_start_date' => 'date',
            'lease_end_date' => 'date',
            'move_in_date' => 'date',
            'activated_at' => 'datetime',
            'ended_at' => 'datetime',
            'rent_amount' => 'decimal:2',
            'service_charge_amount' => 'decimal:2',
            'billing_cycle' => BillingCycle::class,
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'property_unit_id');
    }

    public function rentalApplication(): BelongsTo
    {
        return $this->belongsTo(RentalApplication::class);
    }

    public function tenantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable')->latest();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')->useDisk('local');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('landlord') && $user->landlordProfile) {
            return $query->whereHas('property', fn (Builder $propertyQuery) => $propertyQuery->where('landlord_id', $user->landlordProfile->getKey()));
        }

        if ($user->hasRole('caretaker') && $user->caretakerProfile) {
            return $query->whereHas('property', fn (Builder $propertyQuery) => $propertyQuery->where('caretaker_id', $user->caretakerProfile->getKey()));
        }

        if ($user->hasRole('tenant')) {
            return $query->where('tenant_user_id', $user->getKey());
        }

        return $query->whereRaw('1 = 0');
    }

    public function applyUnitOccupancy(): void
    {
        $occupancy = match ($this->status) {
            TenancyStatus::PendingActivation => UnitOccupancyStatus::Reserved,
            TenancyStatus::Active, TenancyStatus::RenewalPending, TenancyStatus::Ending => UnitOccupancyStatus::Occupied,
            TenancyStatus::Ended => UnitOccupancyStatus::Vacant,
        };

        $this->unit->forceFill([
            'occupancy_status' => $occupancy,
            'available_from' => $this->status === TenancyStatus::Ended ? now()->toDateString() : null,
        ])->save();
    }
}
