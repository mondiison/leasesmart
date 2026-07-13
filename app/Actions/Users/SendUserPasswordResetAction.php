<?php

namespace App\Actions\Users;

use App\Actions\Activity\LogActivityAction;
use App\Models\User;
use Illuminate\Support\Facades\Password;

class SendUserPasswordResetAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    public function execute(User $actor, User $user): string
    {
        $status = Password::broker()->sendResetLink([
            'email' => $user->email,
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->logActivity->execute(
                user: $actor,
                action: 'password_reset_link_sent',
                description: "Sent password reset link to {$user->email}.",
                subject: $user,
            );
        }

        return $status;
    }
}
