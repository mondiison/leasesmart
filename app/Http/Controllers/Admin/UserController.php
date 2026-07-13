<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\CreateUserAction;
use App\Actions\Users\UpdateUserAction;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', [
            'users' => User::query()->with(['roles', 'landlordProfile', 'caretakerProfile', 'tenantProfile'])->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'roles' => Role::cases(),
            'user' => new User(),
            'selectedRole' => old('role', Role::Tenant->value),
            'profile' => [],
        ]);
    }

    public function store(StoreUserRequest $request, CreateUserAction $createUser): RedirectResponse
    {
        $user = $createUser->execute($request->user(), $request->validated());

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $role = $user->primaryRole();

        return view('admin.users.edit', [
            'roles' => Role::cases(),
            'user' => $user->load(['roles', 'landlordProfile', 'caretakerProfile', 'tenantProfile']),
            'selectedRole' => old('role', $role?->value ?? Role::Tenant->value),
            'profile' => $this->profileData($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $updateUser): RedirectResponse
    {
        $updateUser->execute($request->user(), $user, $request->validated());

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'User updated successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function profileData(User $user): array
    {
        if ($user->landlordProfile !== null) {
            return $user->landlordProfile->only(['company_name', 'address', 'notes']);
        }

        if ($user->caretakerProfile !== null) {
            return $user->caretakerProfile->only(['employee_code', 'notes']);
        }

        if ($user->tenantProfile !== null) {
            return $user->tenantProfile->only([
                'full_name',
                'gender',
                'date_of_birth',
                'address',
                'emergency_contact_name',
                'emergency_contact_phone',
                'employment_status',
                'employer_name',
                'monthly_income',
                'notes',
            ]);
        }

        return [];
    }
}
