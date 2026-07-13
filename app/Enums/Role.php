<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Landlord = 'landlord';
    case Caretaker = 'caretaker';
    case Tenant = 'tenant';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $role): string => $role->value,
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Landlord => 'Landlord',
            self::Caretaker => 'Caretaker',
            self::Tenant => 'Tenant',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Oversees platform operations, governance, and account controls.',
            self::Landlord => 'Manages owned properties, tenancy health, and revenue visibility.',
            self::Caretaker => 'Coordinates day-to-day property operations and resident support.',
            self::Tenant => 'Tracks home services, billing, and tenancy information.',
        };
    }
}
