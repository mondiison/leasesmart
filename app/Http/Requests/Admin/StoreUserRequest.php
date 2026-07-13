<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'role' => ['required', Rule::in(Role::values())],
            'password' => ['required', 'confirmed', Password::defaults()],
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
