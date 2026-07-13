<?php

namespace App\Actions\Users;

use App\Actions\Activity\LogActivityAction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateCurrentUserProfileAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(User $user, array $payload, ?UploadedFile $avatar = null): User
    {
        return DB::transaction(function () use ($user, $payload, $avatar): User {
            $user->fill([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'bio' => $payload['bio'] ?? null,
            ]);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            if ($avatar !== null) {
                if ($user->avatar_path !== null) {
                    Storage::disk('public')->delete($user->avatar_path);
                }

                $user->avatar_path = $avatar->storePublicly('avatars', 'public');
            }

            $user->save();

            if ($user->tenantProfile !== null) {
                $user->tenantProfile()->update([
                    'full_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ]);
            }

            $this->logActivity->execute(
                user: $user,
                action: 'profile_updated',
                description: 'Updated personal profile information.',
                subject: $user,
            );

            return $user;
        });
    }
}
