<x-layouts.app>
    <div class="space-y-6">
        <div>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Admin</p>
            <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Create User</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Provision a LeaseSmart account, assign a role, and seed the matching profile record.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @include('admin.users._form', ['mode' => 'create', 'submitLabel' => 'Create Account'])
        </form>
    </div>
</x-layouts.app>
