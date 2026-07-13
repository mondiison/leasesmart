<?php

namespace App\Actions\Users;

use App\Actions\Activity\LogActivityAction;
use App\Enums\Role;
use App\Models\User;
use App\Notifications\IdentityAlertNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    public function __construct(
        protected SyncUserProfileAction $syncUserProfile,
        protected LogActivityAction $logActivity,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(User $actor, User $user, array $payload): User
    {
        return DB::transaction(function () use ($actor, $user, $payload): User {
            $role = Role::from($payload['role']);
            $previousRole = $user->primaryRole()?->value;

            $user->fill([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'bio' => $payload['bio'] ?? null,
                'is_active' => (bool) ($payload['is_active'] ?? false),
            ]);

            if (! empty($payload['password'])) {
                $user->password = Hash::make($payload['password']);
            }

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();
            $user->syncRoles([$role->value]);
            $this->syncUserProfile->execute($user, $role, $payload['profile'] ?? []);

            $this->logActivity->execute(
                user: $actor,
                action: 'user_updated',
                description: "Updated user {$user->email}.",
                subject: $user,
                metadata: ['role' => $role->value],
            );

            if ($previousRole !== $role->value) {
                $this->logActivity->execute(
                    user: $actor,
                    action: 'role_assigned',
                    description: "Assigned {$role->value} role to {$user->email}.",
                    subject: $user,
                    metadata: ['role' => $role->value, 'previous_role' => $previousRole],
                );

                $user->notify(new IdentityAlertNotification(
                    title: 'Your LeaseSmart role changed',
                    message: "Your LeaseSmart access is now configured for the {$role->label()} workspace.",
                    actionUrl: route('dashboard'),
                    actionLabel: 'View Dashboard',
                ));
            }

            return $user;
        });
    }
}
