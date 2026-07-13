<x-layouts.app>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Workspace</p>
                <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Global Search</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Find records across properties, people, billing, leasing, inspections, and support.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('search.index') }}" class="rounded-[1.25rem] border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/60">
            <label for="global-search-page" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Find anything</label>
            <div class="flex flex-col gap-3 sm:flex-row">
                <input id="global-search-page" type="search" name="q" value="{{ $term }}" placeholder="Tenant, property, invoice, payment, issue..." class="min-w-0 flex-1 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                <button type="submit" class="rounded-full bg-zinc-950 px-5 py-3 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">Search</button>
            </div>
        </form>

        @if (mb_strlen($term) > 0 && mb_strlen($term) < 2)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-100">
                Enter at least 2 characters to search.
            </div>
        @endif

        @if (mb_strlen($term) >= 2)
            <div class="space-y-5">
                @forelse ($groups as $group)
                    <section class="overflow-hidden rounded-[1.25rem] border border-zinc-200 bg-white shadow-sm dark:border-white/10 dark:bg-zinc-950/60">
                        <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-white/10 dark:bg-white/5">
                            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $group['title'] }}</h2>
                        </div>
                        <div class="divide-y divide-zinc-200 dark:divide-white/10">
                            @foreach ($group['items'] as $item)
                                <a href="{{ $item['href'] }}" wire:navigate class="flex flex-col gap-3 px-5 py-4 transition hover:bg-zinc-50 dark:hover:bg-white/5 sm:flex-row sm:items-center sm:justify-between">
                                    <span class="min-w-0">
                                        <span class="block break-words text-sm font-semibold text-zinc-950 dark:text-white">{{ $item['title'] }}</span>
                                        <span class="mt-1 block break-words text-sm text-zinc-600 dark:text-zinc-300">{{ $item['meta'] }}</span>
                                    </span>
                                    <span class="w-fit shrink-0 rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-800 dark:bg-cyan-300/15 dark:text-cyan-100">{{ $item['badge'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="rounded-[1.25rem] border border-dashed border-zinc-300 bg-white/70 p-8 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">
                        No matching records were found.
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</x-layouts.app>
