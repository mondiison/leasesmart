<?php

namespace App\Models;

use App\Enums\InspectionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspection extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_id',
        'property_unit_id',
        'requester_user_id',
        'handled_by',
        'status',
        'source',
        'requester_name',
        'requester_email',
        'requester_phone',
        'requested_for_date',
        'requested_for_time',
        'scheduled_at',
        'message',
        'internal_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => InspectionStatus::class,
            'requested_for_date' => 'date',
            'scheduled_at' => 'datetime',
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

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function rentalApplications(): HasMany
    {
        return $this->hasMany(RentalApplication::class);
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

        return $query->whereRaw('1 = 0');
    }
}
