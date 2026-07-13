<x-layouts.app>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Admin</p>
                <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Users and Access</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Manage accounts, roles, profile records, activation state, and password recovery in one place.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">Create User</a>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-[1.75rem] border border-white/70 bg-white/85 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-white/10">
                    <thead class="bg-zinc-50/80 dark:bg-white/5">
                        <tr class="text-left text-xs uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Profile</th>
                            <th class="px-6 py-4">Last Login</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                        @foreach ($users as $managedUser)
                            <tr>
                                <td class="px-6 py-4 align-top">
                                    <p class="font-medium text-zinc-950 dark:text-white">{{ $managedUser->name }}</p>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $managedUser->email }}</p>
                                    @if ($managedUser->phone)
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $managedUser->phone }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-top text-sm text-zinc-700 dark:text-zinc-300">{{ $managedUser->roleLabel() }}</td>
                                <td class="px-6 py-4 align-top">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $managedUser->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-200' : 'bg-rose-100 text-rose-700 dark:bg-rose-400/10 dark:text-rose-200' }}">
                                        {{ $managedUser->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-top text-sm text-zinc-600 dark:text-zinc-300">
                                    @if ($managedUser->landlordProfile)
                                        Landlord profile
                                    @elseif ($managedUser->caretakerProfile)
                                        Caretaker profile
                                    @elseif ($managedUser->tenantProfile)
                                        Tenant profile
                                    @else
                                        No dedicated profile
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-top text-sm text-zinc-600 dark:text-zinc-300">{{ $managedUser->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                                <td class="px-6 py-4 align-top text-right">
                                    <a href="{{ route('admin.users.edit', $managedUser) }}" class="text-sm font-medium text-cyan-700 dark:text-cyan-300">Manage</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-zinc-200 px-6 py-4 dark:border-white/10">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
