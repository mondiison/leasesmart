<?php

namespace App\Support\Navigation;

use App\Enums\Role;
use App\Models\User;

class AppNavigation
{
    public static function for(User $user): array
    {
        $dashboard = static::path('dashboard');
        $role = $user->primaryRole() ?? Role::Tenant;

        $groups = [
            [
                'heading' => 'Workspace',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'icon' => 'layout-grid',
                        'href' => $dashboard,
                        'current' => request()->routeIs('dashboard'),
                    ],
                    [
                        'label' => 'Search',
                        'icon' => 'magnifying-glass',
                        'href' => static::path('search.index'),
                        'current' => request()->routeIs('search.*'),
                    ],
                ],
            ],
            [
                'heading' => 'Modules',
                'items' => match ($role) {
                    Role::Admin => [
                        ['label' => 'Users & Access', 'icon' => 'shield-check', 'href' => static::path('admin.users.index'), 'current' => request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*')],
                        ['label' => 'Properties', 'icon' => 'building-office-2', 'href' => static::path('properties.index'), 'current' => request()->routeIs('properties.*')],
                        ['label' => 'Inspections', 'icon' => 'calendar-days', 'href' => static::path('inspections.index'), 'current' => request()->routeIs('inspections.*')],
                        ['label' => 'Applications', 'icon' => 'clipboard-document-list', 'href' => static::path('applications.index'), 'current' => request()->routeIs('applications.*')],
                        ['label' => 'Tenancies', 'icon' => 'key', 'href' => static::path('tenancies.index'), 'current' => request()->routeIs('tenancies.*')],
                        ['label' => 'Billing', 'icon' => 'banknotes', 'href' => static::path('billing.index'), 'current' => request()->routeIs('billing.*')],
                        ['label' => 'Maintenance', 'icon' => 'wrench-screwdriver', 'href' => static::path('maintenance.index'), 'current' => request()->routeIs('maintenance.*')],
                    ],
                    Role::Landlord => [
                        ['label' => 'Properties', 'icon' => 'building-office-2', 'href' => static::path('properties.index'), 'current' => request()->routeIs('properties.*')],
                        ['label' => 'Inspections', 'icon' => 'calendar-days', 'href' => static::path('inspections.index'), 'current' => request()->routeIs('inspections.*')],
                        ['label' => 'Applications', 'icon' => 'clipboard-document-list', 'href' => static::path('applications.index'), 'current' => request()->routeIs('applications.*')],
                        ['label' => 'Tenancies', 'icon' => 'key', 'href' => static::path('tenancies.index'), 'current' => request()->routeIs('tenancies.*')],
                        ['label' => 'Billing', 'icon' => 'banknotes', 'href' => static::path('billing.index'), 'current' => request()->routeIs('billing.*')],
                        ['label' => 'Maintenance', 'icon' => 'wrench-screwdriver', 'href' => static::path('maintenance.index'), 'current' => request()->routeIs('maintenance.*')],
                    ],
                    Role::Caretaker => [
                        ['label' => 'Properties', 'icon' => 'building-office-2', 'href' => static::path('properties.index'), 'current' => request()->routeIs('properties.*')],
                        ['label' => 'Inspections', 'icon' => 'calendar-days', 'href' => static::path('inspections.index'), 'current' => request()->routeIs('inspections.*')],
                        ['label' => 'Tenancies', 'icon' => 'key', 'href' => static::path('tenancies.index'), 'current' => request()->routeIs('tenancies.*')],
                        ['label' => 'Maintenance', 'icon' => 'wrench-screwdriver', 'href' => static::path('maintenance.index'), 'current' => request()->routeIs('maintenance.*')],
                    ],
                    Role::Tenant => [
                        ['label' => 'Applications', 'icon' => 'clipboard-document-list', 'href' => static::path('applications.index'), 'current' => request()->routeIs('applications.*')],
                        ['label' => 'Tenancy', 'icon' => 'key', 'href' => static::path('tenancies.index'), 'current' => request()->routeIs('tenancies.*')],
                        ['label' => 'Billing', 'icon' => 'banknotes', 'href' => static::path('billing.index'), 'current' => request()->routeIs('billing.*')],
                        ['label' => 'Support', 'icon' => 'lifebuoy', 'href' => static::path('maintenance.index'), 'current' => request()->routeIs('maintenance.*')],
                    ],
                },
            ],
            [
                'heading' => 'Administration',
                'items' => $role === Role::Admin ? [
                    ['label' => 'Roles', 'icon' => 'lock-closed', 'href' => static::path('admin.roles.index'), 'current' => request()->routeIs('admin.roles.*')],
                ] : [],
            ],
            [
                'heading' => 'Account',
                'items' => [
                    [
                        'label' => 'Notifications',
                        'icon' => 'bell',
                        'href' => static::path('notifications.index'),
                        'current' => request()->routeIs('notifications.*'),
                    ],
                    [
                        'label' => 'Settings',
                        'icon' => 'cog-6-tooth',
                        'href' => static::path('settings.profile'),
                        'current' => request()->routeIs('settings.*'),
                    ],
                ],
            ],
        ];

        return array_values(array_filter(
            $groups,
            static fn (array $group): bool => $group['items'] !== [],
        ));
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected static function path(string $name, array $parameters = []): string
    {
        return route($name, $parameters, false);
    }
}
