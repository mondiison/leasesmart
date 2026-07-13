<?php

namespace App\Actions\Users;

use App\Actions\Activity\LogActivityAction;
use App\Models\User;
use App\Notifications\IdentityAlertNotification;

class ToggleUserActiveStateAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    public function execute(User $actor, User $user): User
    {
        $user->forceFill([
            'is_active' => ! $user->is_active,
        ])->save();

        $state = $user->is_active ? 'activated' : 'deactivated';

        $this->logActivity->execute(
            user: $actor,
            action: "user_{$state}",
            description: ucfirst($state)." user {$user->email}.",
            subject: $user,
        );

        $user->notify(new IdentityAlertNotification(
            title: 'Your account status changed',
            message: "Your LeaseSmart account was {$state} by an administrator.",
            actionUrl: route('login'),
            actionLabel: 'Open LeaseSmart',
        ));

        return $user;
    }
}
