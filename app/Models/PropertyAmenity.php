<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PropertyAmenity extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'amenity_property')->withTimestamps();
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(PropertyUnit::class, 'amenity_property_unit')->withTimestamps();
    }
}
