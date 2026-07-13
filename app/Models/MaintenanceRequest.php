<?php

namespace App\Models;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MaintenanceRequest extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'property_id',
        'property_unit_id',
        'tenancy_id',
        'tenant_user_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'reported_at',
        'assigned_to',
        'resolved_at',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'priority' => MaintenancePriority::class,
            'status' => MaintenanceStatus::class,
            'reported_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
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

    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function tenantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(MaintenanceUpdate::class)->latest();
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable')->latest();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
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
            return $query->where(function (Builder $inner) use ($user) {
                $inner
                    ->whereHas('property', fn (Builder $propertyQuery) => $propertyQuery->where('caretaker_id', $user->caretakerProfile->getKey()))
                    ->orWhere('assigned_to', $user->getKey());
            });
        }

        if ($user->hasRole('tenant')) {
            return $query->where('tenant_user_id', $user->getKey());
        }

        return $query->whereRaw('1 = 0');
    }
}
