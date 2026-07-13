<x-layouts.app>
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Inventory</p>
                <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $property->title }}</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Edit the asset, maintain its gallery, and manage rentable units from one place.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('properties.units.create', $property) }}" class="rounded-full border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 dark:border-white/10 dark:text-white">Add Unit</a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-100">{{ session('status') }}</div>
        @endif

        @if ($errors->has('publish_status'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200">{{ $errors->first('publish_status') }}</div>
        @endif

        <form method="POST" action="{{ route('properties.update', $property) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('properties._form', ['submitLabel' => 'Save Changes'])
        </form>

        <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Publish Workflow</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Move the property through review, live listing readiness, and archive states.</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $property->publish_status->badgeClasses() }}">{{ $property->publish_status->label() }}</span>
            </div>
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ($publishStatuses as $status)
                    <form method="POST" action="{{ route('properties.publish-status', $property) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="publish_status" value="{{ $status->value }}">
                        <button type="submit" class="rounded-full border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 dark:border-white/10 dark:text-white">{{ $status->label() }}</button>
                    </form>
                @endforeach
            </div>
        </section>

        <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Units</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Track commercial terms, occupancy, and listing readiness at the unit level.</p>
                </div>
                <a href="{{ route('properties.units.create', $property) }}" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">New Unit</a>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                @forelse ($property->units as $unit)
                    <article class="rounded-3xl border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-zinc-950 dark:text-white">{{ $unit->unit_name }}</h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $unit->unit_code ?: 'No unit code yet' }}</p>
                            </div>
                            <span class="rounded-full bg-zinc-950/5 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-white/10 dark:text-zinc-200">{{ $unit->occupancy_status->label() }}</span>
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 text-sm text-zinc-700 dark:text-zinc-300">
                            <p>Rent: NGN {{ number_format((float) $unit->rent_amount, 2) }}</p>
                            <p>Billing: {{ $unit->billing_cycle->label() }}</p>
                            <p>Listed: {{ $unit->is_listed ? 'Yes' : 'No' }}</p>
                            <p>Available: {{ $unit->available_from?->format('M j, Y') ?? 'On request' }}</p>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ $unit->amenities->count() }} amenities</p>
                            <a href="{{ route('properties.units.edit', [$property, $unit]) }}" class="text-sm font-medium text-cyan-700 dark:text-cyan-300">Edit unit</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-white/10 dark:text-zinc-300 lg:col-span-2">No units yet. Add one listed unit before publishing this property live.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
