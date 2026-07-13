<x-layouts.app>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Account</p>
                <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Notifications</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Track application, billing, maintenance, lease, and identity updates from one inbox.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5">
                    <span class="font-semibold text-zinc-950 dark:text-white">{{ number_format($unreadCount) }}</span>
                    <span class="text-zinc-600 dark:text-zinc-300">unread</span>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5">
                    <span class="font-semibold text-zinc-950 dark:text-white">{{ number_format($totalCount) }}</span>
                    <span class="text-zinc-600 dark:text-zinc-300">total</span>
                </div>
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" @disabled($unreadCount === 0) class="rounded-full border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-900 transition hover:border-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/30">Mark all read</button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="space-y-4">
            @forelse ($notifications as $notification)
                <article @class([
                    'rounded-[1.5rem] border p-5 shadow-lg shadow-cyan-950/5',
                    'border-cyan-200 bg-cyan-50/80 dark:border-cyan-400/20 dark:bg-cyan-400/10' => $notification->unread(),
                    'border-white/70 bg-white/85 dark:border-white/10 dark:bg-white/5' => $notification->read(),
                ])>
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($notification->unread())
                                    <span class="rounded-full bg-cyan-700 px-2.5 py-1 text-xs font-semibold text-white dark:bg-cyan-300 dark:text-cyan-950">Unread</span>
                                @endif
                                <h2 class="break-words font-semibold text-zinc-950 dark:text-white">{{ $notification->data['title'] ?? 'Notification' }}</h2>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $notification->data['message'] ?? 'LeaseSmart update.' }}</p>
                            @if (!empty($notification->data['action_url']) && !empty($notification->data['action_label']))
                                <a href="{{ route('notifications.open', $notification) }}" class="mt-3 inline-flex text-sm font-medium text-cyan-700 dark:text-cyan-300">{{ $notification->data['action_label'] }}</a>
                            @endif
                        </div>
                        <span class="shrink-0 text-xs text-zinc-500 dark:text-zinc-400">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                </article>
            @empty
                <div class="rounded-[1.5rem] border border-dashed border-zinc-300 bg-zinc-50/80 p-6 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
                    No notifications yet.
                </div>
            @endforelse
        </div>

        {{ $notifications->links() }}
    </div>
</x-layouts.app>
