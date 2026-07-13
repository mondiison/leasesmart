<?php

namespace App\Models;

use App\Enums\RentalApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class RentalApplication extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_id',
        'property_unit_id',
        'inspection_id',
        'applicant_user_id',
        'reviewed_by',
        'status',
        'source',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'employment_status',
        'employer_name',
        'monthly_income',
        'preferred_move_in_date',
        'message',
        'review_notes',
        'submitted_at',
        'decided_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RentalApplicationStatus::class,
            'monthly_income' => 'decimal:2',
            'preferred_move_in_date' => 'date',
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
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

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function tenancy(): HasOne
    {
        return $this->hasOne(Tenancy::class);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable')->latest();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('landlord') && $user->landlordProfile) {
            return $query->whereHas('property', fn (Builder $propertyQuery) => $propertyQuery->where('landlord_id', $user->landlordProfile->getKey()));
        }

        return $query->whereRaw('1 = 0');
    }
}
