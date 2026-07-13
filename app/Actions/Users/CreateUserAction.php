<?php

namespace App\Actions\Users;

use App\Actions\Activity\LogActivityAction;
use App\Enums\Role;
use App\Models\User;
use App\Notifications\IdentityAlertNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function __construct(
        protected SyncUserProfileAction $syncUserProfile,
        protected LogActivityAction $logActivity,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(User $actor, array $payload): User
    {
        return DB::transaction(function () use ($actor, $payload): User {
            $role = Role::from($payload['role']);

            $user = User::query()->create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'bio' => $payload['bio'] ?? null,
                'password' => Hash::make($payload['password']),
                'is_active' => (bool) ($payload['is_active'] ?? true),
                'email_verified_at' => now(),
            ]);

            $user->syncRoles([$role->value]);
            $this->syncUserProfile->execute($user, $role, $payload['profile'] ?? []);

            $this->logActivity->execute(
                user: $actor,
                action: 'user_created',
                description: "Created user {$user->email}.",
                subject: $user,
                metadata: ['role' => $role->value],
            );

            $this->logActivity->execute(
                user: $actor,
                action: 'role_assigned',
                description: "Assigned {$role->value} role to {$user->email}.",
                subject: $user,
                metadata: ['role' => $role->value],
            );

            $user->notify(new IdentityAlertNotification(
                title: 'Your LeaseSmart account is ready',
                message: "An administrator created your LeaseSmart account as {$role->label()}. You can sign in right away.",
                actionUrl: route('login'),
                actionLabel: 'Open LeaseSmart',
            ));

            return $user;
        });
    }
}
