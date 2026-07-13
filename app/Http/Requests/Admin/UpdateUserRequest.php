<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $managedUser */
        $managedUser = $this->route('user');

        return $this->user()?->can('update', $managedUser) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $managedUser */
        $managedUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($managedUser->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'role' => ['required', Rule::in(Role::values())],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'is_active' => ['sometimes', 'boolean'],
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
}
