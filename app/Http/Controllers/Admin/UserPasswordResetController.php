<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\SendUserPasswordResetAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class UserPasswordResetController extends Controller
{
    public function __invoke(User $user, SendUserPasswordResetAction $sendReset): RedirectResponse
    {
        $this->authorize('sendPasswordReset', $user);

        $status = $sendReset->execute(request()->user(), $user);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', __($status === Password::RESET_LINK_SENT ? $status : 'Unable to send a reset link right now.'));
    }
}
