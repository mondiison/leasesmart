<x-layouts.app>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Admin</p>
                <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Manage {{ $user->name }}</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Adjust role access, update profile records, toggle account state, and trigger password recovery.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.users.status', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="rounded-full border border-zinc-300 px-4 py-2 text-sm font-medium dark:border-white/10">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.users.password-reset', $user) }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-zinc-300 px-4 py-2 text-sm font-medium dark:border-white/10">Send Reset Link</button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @method('PUT')
            @include('admin.users._form', ['mode' => 'edit', 'submitLabel' => 'Save Changes'])
        </form>
    </div>
</x-layouts.app>
