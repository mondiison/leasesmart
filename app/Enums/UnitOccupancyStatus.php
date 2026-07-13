<?php

namespace App\Enums;

enum UnitOccupancyStatus: string
{
    case Vacant = 'vacant';
    case Reserved = 'reserved';
    case Occupied = 'occupied';
    case Unavailable = 'unavailable';
    case UnderMaintenance = 'under_maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Vacant => 'Vacant',
            self::Reserved => 'Reserved',
            self::Occupied => 'Occupied',
            self::Unavailable => 'Unavailable',
            self::UnderMaintenance => 'Under Maintenance',
        };
    }
}
