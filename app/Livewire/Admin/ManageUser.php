<?php

namespace App\Livewire\Admin;

use App\Actions\Users\CreateUserAction;
use App\Actions\Users\SendUserPasswordResetAction;
use App\Actions\Users\ToggleUserActiveStateAction;
use App\Actions\Users\UpdateUserAction;
use App\Enums\Role;
use App\Livewire\Concerns\InteractsWithFluxToast;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Component;

class ManageUser extends Component
{
    use AuthorizesRequests, InteractsWithFluxToast;

    public ?User $managedUser = null;

    public string $name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?string $bio = null;
    public string $role = 'tenant';
    public ?string $password = null;
    public ?string $password_confirmation = null;
    public bool $is_active = true;
    public array $profile = [];

    public function mount(?User $user = null): void
    {
        if ($user?->exists) {
            $this->authorize('update', $user);
            $this->managedUser = $user->load(['roles', 'landlordProfile', 'caretakerProfile', 'tenantProfile']);
            $this->fillFromUser($this->managedUser);

            return;
        }

        $this->authorize('create', User::class);
        $this->profile = [];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->managedUser)],
            'phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'role' => ['required', Rule::in(Role::values())],
            'password' => [$this->managedUser ? 'nullable' : 'required', 'confirmed', PasswordRule::defaults()],
            'is_active' => ['boolean'],
            'profile.company_name' => ['nullable', 'string', 'max:255'],
            'profile.address' => ['nullable', 'string', 'max:1000'],
            'profile.employee_code' => ['nullable', 'string', 'max:100'],
            'profile.notes' => ['nullable', 'string', 'max:1000'],
            'profile.full_name' => ['nullable', 'string', 'max:255'],
            'profile.gender' => ['nullable', 'string', 'max:50'],
            'profile.date_of_birth' => ['nullable', 'date'],
            'profile.emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'profile.emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'profile.employment_status' => ['nullable', 'string', 'max:255'],
            'profile.employer_name' => ['nullable', 'string', 'max:255'],
            'profile.monthly_income' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function updated(string $property): void
    {
        if (
            in_array($property, ['name', 'email', 'phone', 'bio', 'role', 'password', 'password_confirmation'], true)
            || str_starts_with($property, 'profile.')
        ) {
            $this->validateOnly($property);
        }
    }

    public function getCanSubmitProperty(): bool
    {
        if (blank($this->name) || blank($this->email) || blank($this->role)) {
            return false;
        }

        if (! $this->managedUser?->exists) {
            return filled($this->password) && filled($this->password_confirmation);
        }

        return true;
    }

    public function save(CreateUserAction $createUser, UpdateUserAction $updateUser)
    {
        $validated = $this->validate();

        if ($this->managedUser?->exists) {
            $user = $updateUser->execute(auth()->user(), $this->managedUser, $validated);
            $this->managedUser = $user->load(['roles', 'landlordProfile', 'caretakerProfile', 'tenantProfile']);
            $this->fillFromUser($this->managedUser);
            $this->toast('User updated successfully.');

            return $this->redirectRoute('admin.users.edit', $user, navigate: true);
        }

        $user = $createUser->execute(auth()->user(), $validated);
        $this->toast('User created successfully.');

        return $this->redirectRoute('admin.users.edit', $user, navigate: true);
    }

    public function toggleActive(ToggleUserActiveStateAction $toggle)
    {
        if (! $this->managedUser?->exists) {
            return null;
        }

        $this->authorize('toggleActive', $this->managedUser);
        $this->managedUser = $toggle->execute(auth()->user(), $this->managedUser)->fresh(['roles', 'landlordProfile', 'caretakerProfile', 'tenantProfile']);
        $this->fillFromUser($this->managedUser);
        $this->toast('Account status updated.');

        return null;
    }

    public function sendPasswordReset(SendUserPasswordResetAction $sendReset)
    {
        if (! $this->managedUser?->exists) {
            return null;
        }

        $this->authorize('sendPasswordReset', $this->managedUser);
        $status = $sendReset->execute(auth()->user(), $this->managedUser);
        $successful = $status === Password::RESET_LINK_SENT;

        $this->toast(
            __($successful ? $status : 'Unable to send a reset link right now.'),
            $successful ? 'Email Sent' : 'Action Needed',
            $successful ? 'success' : 'warning',
        );

        return null;
    }

    public function render()
    {
        return view('livewire.admin.manage-user', [
            'roles' => Role::cases(),
            'roleModel' => $this->role,
        ])->layout('components.layouts.app');
    }

    protected function fillFromUser(User $user): void
    {
        $role = $user->primaryRole();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->bio = $user->bio;
        $this->role = $role?->value ?? Role::Tenant->value;
        $this->password = null;
        $this->password_confirmation = null;
        $this->is_active = $user->is_active;
        $this->profile = $this->profileData($user);
    }

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
