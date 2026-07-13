    <div class="space-y-6">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" wire:navigate class="font-medium text-zinc-700 transition hover:text-cyan-700 dark:text-zinc-300 dark:hover:text-cyan-200">Dashboard</a>
            <span aria-hidden="true">/</span>
            <span class="font-medium text-zinc-950 dark:text-white" aria-current="page">Properties</span>
        </nav>

        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Inventory</p>
                <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Property Portfolio</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Manage buildings, assignments, listing readiness, and rental units.</p>
            </div>
            @can('create', App\Models\Property::class)
                <a href="{{ route('properties.create') }}" wire:navigate class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">Create Property</a>
            @endcan
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-100">{{ session('status') }}</div>
        @endif

        <div class="grid gap-5 xl:grid-cols-2">
            @forelse ($properties as $property)
                <article class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ $property->title }}</h2>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $property->address_line_1 }}, {{ $property->city }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $property->publish_status->badgeClasses() }}">{{ $property->publish_status->label() }}</span>
                    </div>

                    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div><dt class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Type</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $property->property_type->label() }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Units</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $property->units_count }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Landlord</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $property->landlord?->user?->name ?? 'Unassigned' }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Caretaker</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $property->caretaker?->user?->name ?? 'Unassigned' }}</dd></div>
                    </dl>

                    @can('update', $property)
                        <div class="mt-6 flex items-center justify-between gap-3">
                            <a href="{{ route('properties.edit', $property) }}" wire:navigate class="text-sm font-medium text-cyan-700 dark:text-cyan-300">Manage property</a>
                            @if ($property->published_at)
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">Published {{ $property->published_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    @endcan
                </article>
            @empty
                <div class="rounded-[1.75rem] border border-dashed border-zinc-300 bg-white/70 p-8 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300 xl:col-span-2">No properties yet. Create the first portfolio entry to start managing listings and units.</div>
            @endforelse
        </div>

        <div>{{ $properties->links() }}</div>
    </div>
