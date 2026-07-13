<?php

namespace App\Domain\Access;

use App\Enums\Role;

class PermissionMatrix
{
    /**
     * @return array<string, list<string>>
     */
    public static function definitions(): array
    {
        return [
            Role::Admin->value => [
                'dashboard.view',
                'identity.manage',
                'properties.manage',
                'inspections.manage',
                'applications.manage',
                'tenancies.manage',
                'billing.manage',
                'maintenance.manage',
                'reports.view',
                'api.access',
            ],
            Role::Landlord->value => [
                'dashboard.view',
                'properties.manage',
                'inspections.manage',
                'applications.review',
                'tenancies.view',
                'billing.view',
                'maintenance.view',
                'reports.view',
            ],
            Role::Caretaker->value => [
                'dashboard.view',
                'properties.view',
                'inspections.manage',
                'maintenance.manage',
                'tenancies.view',
            ],
            Role::Tenant->value => [
                'dashboard.view',
                'tenancies.view',
                'billing.view',
                'maintenance.create',
                'maintenance.view',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function permissions(): array
    {
        return array_values(
            array_unique(
                array_merge(...array_values(self::definitions())),
            ),
        );
    }

    /**
     * @return list<string>
     */
    public static function forRole(Role $role): array
    {
        return match ($role) {
            Role::Landlord => array_merge(self::definitions()[$role->value] ?? [], ['applications.manage', 'tenancies.manage']),
            default => self::definitions()[$role->value] ?? [],
        };
    }
}
