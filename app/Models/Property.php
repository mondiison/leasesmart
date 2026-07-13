<?php

namespace App\Models;

use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Property extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'landlord_id',
        'caretaker_id',
        'title',
        'slug',
        'property_code',
        'property_type',
        'description',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'year_built',
        'publish_status',
        'is_featured',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'property_type' => PropertyType::class,
            'publish_status' => PropertyPublishStatus::class,
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class);
    }

    public function caretaker(): BelongsTo
    {
        return $this->belongsTo(Caretaker::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function units(): HasMany
    {
        return $this->hasMany(PropertyUnit::class);
    }

    public function publicUnits(): HasMany
    {
        return $this->hasMany(PropertyUnit::class)->publiclyVisible();
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function rentalApplications(): HasMany
    {
        return $this->hasMany(RentalApplication::class);
    }

    public function tenancies(): HasMany
    {
        return $this->hasMany(Tenancy::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(PropertyAmenity::class, 'amenity_property')->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('publish_status', PropertyPublishStatus::Published);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->published()->whereHas('publicUnits');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('landlord') && $user->landlordProfile) {
            return $query->where('landlord_id', $user->landlordProfile->getKey());
        }

        if ($user->hasRole('caretaker') && $user->caretakerProfile) {
            return $query->where('caretaker_id', $user->caretakerProfile->getKey());
        }

        if ($user->hasRole('tenant')) {
            return $query->whereHas('tenancies', fn (Builder $tenancyQuery) => $tenancyQuery->where('tenant_user_id', $user->getKey()));
        }

        return $query->whereRaw('1 = 0');
    }

    public function isPubliclyVisible(): bool
    {
        return $this->publish_status === PropertyPublishStatus::Published && $this->publicUnits()->exists();
    }
}
