<div class="space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Admin</p>
            <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $managedUser ? 'Manage User' : 'Create User' }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Provision a LeaseSmart account, assign a role, and keep the matching profile record in sync.</p>
        </div>
        @if ($managedUser)
            <div class="flex flex-wrap gap-3">
                <button type="button" wire:click="toggleActive" class="rounded-full border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 dark:border-white/10 dark:text-white">{{ $is_active ? 'Deactivate' : 'Activate' }}</button>
                <button type="button" wire:click="sendPasswordReset" class="rounded-full border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 dark:border-white/10 dark:text-white">Send Reset Link</button>
            </div>
        @endif
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
            <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Core Account</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Name</label><input type="text" wire:model.live.debounce.300ms="name" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Email</label><input type="email" wire:model.live.debounce.300ms="email" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Phone</label><input type="text" wire:model.blur="phone" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Primary Role</label><select wire:model.live="role" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@foreach ($roles as $item)<option value="{{ $item->value }}">{{ $item->label() }}</option>@endforeach</select>@error('role') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                    <div class="md:col-span-2"><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Bio</label><textarea wire:model.blur="bio" rows="4" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></textarea>@error('bio') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                </div>
            </section>

            <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Security</h2>
                <div class="mt-5 grid gap-5">
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $managedUser ? 'New Password' : 'Password' }}</label><input type="password" wire:model.live.debounce.300ms="password" @if (! $managedUser) required @endif class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Confirm Password</label><input type="password" wire:model.live.debounce.300ms="password_confirmation" @if (! $managedUser) required @endif class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                    <label class="inline-flex items-center gap-3 rounded-2xl border border-zinc-200 px-4 py-3 text-sm dark:border-white/10"><input type="checkbox" wire:model="is_active"><span>Account is active</span></label>
                </div>
            </section>
        </div>

        <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Role-Specific Profile</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">The visible fields stay aligned with the selected role so profile setup and access configuration stay together.</p>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div class="rounded-3xl border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
                    <h3 class="font-semibold text-zinc-950 dark:text-white">Landlord Profile</h3>
                    <div class="mt-4 space-y-4">
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Company Name</label><input type="text" wire:model.blur="profile.company_name" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Address</label><textarea wire:model.blur="profile.address" rows="3" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></textarea></div>
                    </div>
                </div>
                <div class="rounded-3xl border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
                    <h3 class="font-semibold text-zinc-950 dark:text-white">Caretaker Profile</h3>
                    <div class="mt-4 space-y-4">
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Employee Code</label><input type="text" wire:model.blur="profile.employee_code" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Notes</label><textarea wire:model.blur="profile.notes" rows="3" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></textarea></div>
                    </div>
                </div>
                <div class="rounded-3xl border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
                    <h3 class="font-semibold text-zinc-950 dark:text-white">Tenant Profile</h3>
                    <div class="mt-4 space-y-4">
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Full Name</label><input type="text" wire:model.blur="profile.full_name" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Employment Status</label><input type="text" wire:model.blur="profile.employment_status" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Employer</label><input type="text" wire:model.blur="profile.employer_name" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Monthly Income</label><input type="number" step="0.01" wire:model.blur="profile.monthly_income" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                    </div>
                </div>
            </div>

            <div class="mt-6"><button type="submit" @disabled(! $this->canSubmit) wire:loading.attr="disabled" class="rounded-full bg-zinc-950 px-5 py-2.5 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">{{ $managedUser ? 'Save Changes' : 'Create Account' }}</button></div>
        </section>
    </form>
</div>
