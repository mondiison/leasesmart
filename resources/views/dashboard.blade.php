<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <section class="relative overflow-hidden rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5 md:p-8">
            <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,rgba(14,165,233,0.22),transparent_55%)] dark:bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.15),transparent_45%)]"></div>
            <div class="relative flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-cyan-700 dark:text-cyan-300">{{ $hero['eyebrow'] }}</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white md:text-4xl">{{ $hero['title'] }}</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-zinc-600 dark:text-zinc-300">{{ $hero['description'] }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3 xl:max-w-sm xl:justify-end">
                    <span class="rounded-full bg-zinc-950 px-4 py-2 text-xs font-medium text-white dark:bg-white dark:text-zinc-950">
                        {{ $role->label() }} Workspace
                    </span>
                    <span class="rounded-full border border-cyan-200 bg-cyan-50 px-4 py-2 text-xs font-medium text-cyan-800 dark:border-cyan-400/20 dark:bg-cyan-400/10 dark:text-cyan-200">
                        Live reporting enabled
                    </span>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <article class="rounded-[1.75rem] border border-white/70 bg-white/80 p-5 shadow-lg shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $stat['detail'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Command Center</p>
                    <h2 class="mt-1 text-xl font-semibold tracking-tight text-zinc-950 dark:text-white">Needs attention now</h2>
                </div>
                <p class="max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-300">Live queues pulled from leasing, billing, tenancy, and support records so each role can open the next useful workspace quickly.</p>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
                @foreach ($commandCenter as $queue)
                    <article class="flex min-h-full flex-col rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="break-words text-sm font-semibold text-zinc-950 dark:text-white">{{ $queue['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $queue['summary'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-zinc-950 px-3 py-1 text-sm font-semibold text-white dark:bg-white dark:text-zinc-950">{{ $queue['count'] }}</span>
                        </div>

                        <div class="mt-5 flex-1 space-y-3">
                            @forelse ($queue['items'] as $item)
                                <div class="rounded-2xl border border-zinc-200 bg-white p-3 dark:border-white/10 dark:bg-zinc-950/60">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="break-words text-sm font-medium text-zinc-950 dark:text-white">{{ $item['label'] }}</p>
                                            <p class="mt-1 break-words text-xs leading-5 text-zinc-600 dark:text-zinc-300">{{ $item['meta'] }}</p>
                                        </div>
                                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $item['badge_classes'] }}">{{ $item['badge'] }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white/70 p-3 text-sm leading-6 text-zinc-600 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
                                    {{ $queue['empty'] }}
                                </div>
                            @endforelse
                        </div>

                        <a href="{{ $queue['href'] }}" wire:navigate class="mt-5 inline-flex text-sm font-semibold text-cyan-700 transition hover:text-cyan-900 dark:text-cyan-300 dark:hover:text-cyan-100">
                            {{ $queue['cta'] }}
                        </a>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,1fr)]">
            <div class="space-y-6">
                @foreach ($reportGroups as $group)
                    <section class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Reporting Slice</p>
                                <h2 class="mt-1 text-xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $group['title'] }}</h2>
                            </div>
                            <p class="max-w-xl text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $group['summary'] }}</p>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            @foreach ($group['metrics'] as $metric)
                                <article class="rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $metric['label'] }}</p>
                                    <p class="mt-3 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $metric['value'] }}</p>
                                    <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $metric['detail'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <aside class="space-y-6">
                <section class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Priority Queue</p>
                            <h2 class="mt-1 text-xl font-semibold tracking-tight text-zinc-950 dark:text-white">What to open next</h2>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        @foreach ($focusItems as $item)
                            <article class="rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
                                <h3 class="text-base font-semibold text-zinc-950 dark:text-white">{{ $item['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $item['description'] }}</p>
                                <a href="{{ $item['href'] }}" wire:navigate class="mt-4 inline-flex text-sm font-semibold text-cyan-700 transition hover:text-cyan-900 dark:text-cyan-300 dark:hover:text-cyan-100">
                                    {{ $item['cta'] }}
                                </a>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-[2rem] border border-cyan-200/80 bg-linear-to-br from-cyan-50 to-white p-6 shadow-xl shadow-cyan-950/5 dark:border-cyan-400/20 dark:bg-linear-to-br dark:from-cyan-400/10 dark:to-white/5">
                    <p class="text-sm font-medium text-cyan-700 dark:text-cyan-200">Recent Activity</p>
                    <div class="mt-5 space-y-4">
                        @forelse ($activity as $item)
                            <a href="{{ $item['href'] }}" wire:navigate class="block rounded-[1.25rem] border border-cyan-200/80 bg-white/85 p-4 transition hover:border-cyan-300 hover:bg-white dark:border-cyan-400/15 dark:bg-white/5 dark:hover:border-cyan-300/30">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">{{ $item['title'] }}</h3>
                                        <p class="mt-1 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $item['meta'] }}</p>
                                    </div>
                                    <span class="shrink-0 text-xs font-medium uppercase tracking-[0.18em] text-cyan-700 dark:text-cyan-200">{{ $item['time'] }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-[1.25rem] border border-dashed border-cyan-200 bg-white/80 p-4 text-sm leading-6 text-zinc-600 dark:border-cyan-400/20 dark:bg-white/5 dark:text-zinc-300">
                                Activity will appear here as new inspections, invoices, maintenance updates, and tenancy events are recorded.
                            </div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </section>
    </div>
</x-layouts.app>
