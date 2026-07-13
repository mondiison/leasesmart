<?php

namespace App\Enums;

enum PropertyType: string
{
    case ApartmentBuilding = 'apartment_building';
    case Duplex = 'duplex';
    case Bungalow = 'bungalow';
    case DetachedHouse = 'detached_house';
    case Terrace = 'terrace';
    case SelfContain = 'self_contain';
    case Commercial = 'commercial';
    case MixedUse = 'mixed_use';
    case Land = 'land';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $type): string => $type->value,
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::ApartmentBuilding => 'Apartment Building',
            self::Duplex => 'Duplex',
            self::Bungalow => 'Bungalow',
            self::DetachedHouse => 'Detached House',
            self::Terrace => 'Terrace',
            self::SelfContain => 'Self Contain',
            self::Commercial => 'Commercial',
            self::MixedUse => 'Mixed Use',
            self::Land => 'Land',
        };
    }
}
