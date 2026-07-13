@csrf

<div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
    <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Core Account</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" required>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" required>
                @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                @error('phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Primary Role</label>
                <select name="role" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $selectedRole) === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
                @error('role') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Bio</label>
                <textarea name="bio" rows="4" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">{{ old('bio', $user->bio) }}</textarea>
                @error('bio') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Security</h2>
        <div class="mt-5 grid gap-5">
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $mode === 'create' ? 'Password' : 'New Password' }}</label>
                <input type="password" name="password" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" {{ $mode === 'create' ? 'required' : '' }}>
                @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Confirm Password</label>
                <input type="password" name="password_confirmation" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" {{ $mode === 'create' ? 'required' : '' }}>
            </div>
            <label class="inline-flex items-center gap-3 rounded-2xl border border-zinc-200 px-4 py-3 text-sm dark:border-white/10">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
                <span>Account is active</span>
            </label>
        </div>
    </section>
</div>

<section class="mt-6 rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Role-Specific Profiles</h2>
    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Keep landlord, caretaker, and tenant profile details aligned with the account record.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
            <h3 class="font-semibold text-zinc-950 dark:text-white">Landlord Profile</h3>
            <div class="mt-4 space-y-4">
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Company Name</label>
                    <input type="text" name="profile[company_name]" value="{{ old('profile.company_name', $profile['company_name'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Address</label>
                    <textarea name="profile[address]" rows="3" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">{{ old('profile.address', $profile['address'] ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
            <h3 class="font-semibold text-zinc-950 dark:text-white">Caretaker Profile</h3>
            <div class="mt-4 space-y-4">
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Employee Code</label>
                    <input type="text" name="profile[employee_code]" value="{{ old('profile.employee_code', $profile['employee_code'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Notes</label>
                    <textarea name="profile[notes]" rows="3" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">{{ old('profile.notes', $profile['notes'] ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
            <h3 class="font-semibold text-zinc-950 dark:text-white">Tenant Profile</h3>
            <div class="mt-4 space-y-4">
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Full Name</label>
                    <input type="text" name="profile[full_name]" value="{{ old('profile.full_name', $profile['full_name'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Employment Status</label>
                    <input type="text" name="profile[employment_status]" value="{{ old('profile.employment_status', $profile['employment_status'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Employer</label>
                    <input type="text" name="profile[employer_name]" value="{{ old('profile.employer_name', $profile['employer_name'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Monthly Income</label>
                    <input type="number" step="0.01" name="profile[monthly_income]" value="{{ old('profile.monthly_income', $profile['monthly_income'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <button type="submit" class="rounded-full bg-zinc-950 px-5 py-2.5 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">{{ $submitLabel }}</button>
    </div>
</section>
