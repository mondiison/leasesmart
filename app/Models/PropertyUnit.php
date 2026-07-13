<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\UnitOccupancyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PropertyUnit extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_id',
        'unit_code',
        'unit_name',
        'unit_type',
        'floor_label',
        'bedrooms',
        'bathrooms',
        'toilets',
        'size_sqm',
        'occupancy_status',
        'rent_amount',
        'billing_cycle',
        'service_charge_amount',
        'caution_fee_amount',
        'inspection_fee_amount',
        'available_from',
        'description',
        'is_listed',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occupancy_status' => UnitOccupancyStatus::class,
            'billing_cycle' => BillingCycle::class,
            'available_from' => 'date',
            'is_listed' => 'boolean',
            'rent_amount' => 'decimal:2',
            'service_charge_amount' => 'decimal:2',
            'caution_fee_amount' => 'decimal:2',
            'inspection_fee_amount' => 'decimal:2',
            'size_sqm' => 'decimal:2',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(PropertyAmenity::class, 'amenity_property_unit')->withTimestamps();
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'property_unit_id');
    }

    public function rentalApplications(): HasMany
    {
        return $this->hasMany(RentalApplication::class, 'property_unit_id');
    }

    public function tenancies(): HasMany
    {
        return $this->hasMany(Tenancy::class, 'property_unit_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('is_listed', true)
            ->whereIn('occupancy_status', [
                UnitOccupancyStatus::Vacant,
                UnitOccupancyStatus::Reserved,
            ]);
    }
}
