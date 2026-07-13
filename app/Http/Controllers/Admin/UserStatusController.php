<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\ToggleUserActiveStateAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserStatusController extends Controller
{
    public function __invoke(User $user, ToggleUserActiveStateAction $toggle): RedirectResponse
    {
        $this->authorize('toggleActive', $user);

        $toggle->execute(request()->user(), $user);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'Account status updated.');
    }
}
