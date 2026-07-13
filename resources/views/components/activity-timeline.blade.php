@props([
    'logs',
    'empty' => 'No activity has been recorded yet.',
])

@php
    $items = collect($logs)->sortByDesc('created_at')->values();
@endphp

<section {{ $attributes->class('rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70') }}>
    <h4 class="text-sm font-semibold text-zinc-950 dark:text-white">Activity timeline</h4>

    <div class="mt-4 space-y-4">
        @forelse ($items as $log)
            <div class="relative border-l border-zinc-200 pl-4 dark:border-white/10">
                <span class="absolute -left-[5px] top-1.5 h-2.5 w-2.5 rounded-full bg-emerald-600 dark:bg-emerald-300"></span>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ \App\Support\Activity\ActivityPresenter::label($log->action) }}</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $log->created_at?->diffForHumans() }}</p>
                </div>
                @if ($log->description)
                    <p class="mt-2 whitespace-pre-line break-words text-sm text-zinc-700 dark:text-zinc-200">{{ $log->description }}</p>
                @endif
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">{{ $log->user?->name ?? 'System' }}</p>
            </div>
        @empty
            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $empty }}</p>
        @endforelse
    </div>
</section>
