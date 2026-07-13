<div class="space-y-6">
    <div>
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Admin</p>
        <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Roles and Permissions</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Users & Access now uses a Livewire-first admin surface while still exposing the current permission matrix for review.</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($roles as $role)
            <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ str($role->name)->headline() }}</h2>
                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-white/10 dark:text-zinc-200">{{ $role->permissions->count() }} permissions</span>
                </div>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($role->permissions as $permission)
                        <span class="rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-medium text-cyan-800 dark:border-cyan-400/20 dark:bg-cyan-400/10 dark:text-cyan-200">{{ $permission->name }}</span>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</div>
