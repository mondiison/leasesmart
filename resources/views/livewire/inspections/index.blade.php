<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Operations</p>
            <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Inspections</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Manage viewing requests coming in from the public marketplace and keep requesters updated.</p>
        </div>
        <div class="grid gap-3 sm:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Search</label>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Requester, property, unit" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
            </div>
            <div>
                <label for="inspection-filter" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Status filter</label>
                <select id="inspection-filter" wire:model.live="status" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Focus</label>
                <select wire:model.live="focus" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
                    <option value="">All requests</option>
                    <option value="upcoming">Upcoming only</option>
                </select>
            </div>
            <div class="sm:col-span-3">
                <a href="{{ route('exports.show', ['type' => 'inspections', 'q' => $search, 'status' => $status, 'focus' => $focus]) }}" class="inline-flex rounded-full border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-900 transition hover:border-zinc-950 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/30">Export CSV</a>
                <a href="{{ route('reports.premium', ['type' => 'inspections', 'q' => $search, 'status' => $status, 'focus' => $focus]) }}" target="_blank" class="ml-2 inline-flex rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">Premium PDF</a>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        @forelse ($inspections as $inspection)
            <article class="overflow-hidden rounded-[1.25rem] border border-zinc-200 bg-white shadow-sm dark:border-white/10 dark:bg-zinc-950/60">
                <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-white/10 dark:bg-white/5">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Inspection request</p>
                            <h2 class="mt-1 break-words text-lg font-semibold text-zinc-950 dark:text-white">{{ $inspection->property->title }}</h2>
                            <p class="mt-1 break-words text-sm text-zinc-600 dark:text-zinc-300">
                                {{ $inspection->unit?->unit_name ?? 'Unit not specified' }}{{ $inspection->unit?->unit_type ? ' - '.$inspection->unit->unit_type : '' }}
                            </p>
                        </div>

                        <span class="inline-flex w-fit shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $inspection->status->badgeClasses() }}">{{ $inspection->status->label() }}</span>
                    </div>
                </div>

                <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_420px]">
                    <div class="min-w-0 space-y-5 p-5">
                        <div class="grid gap-4 md:grid-cols-2">
                            <section class="min-w-0 rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">Requester</h3>
                                <div class="mt-3 space-y-3 text-sm">
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Name</p>
                                        <p class="mt-1 break-words text-zinc-900 dark:text-zinc-100">{{ $inspection->requester_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Email</p>
                                        <p class="mt-1 break-all text-zinc-900 dark:text-zinc-100">{{ $inspection->requester_email }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Phone</p>
                                        <p class="mt-1 break-words text-zinc-900 dark:text-zinc-100">{{ $inspection->requester_phone ?: 'Not provided' }}</p>
                                    </div>
                                </div>
                            </section>

                            <section class="min-w-0 rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">Schedule and Assignment</h3>
                                <div class="mt-3 space-y-3 text-sm">
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Preferred date</p>
                                        <p class="mt-1 text-zinc-900 dark:text-zinc-100">{{ $inspection->requested_for_date?->format('M j, Y') ?? 'Open date' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Preferred time</p>
                                        <p class="mt-1 text-zinc-900 dark:text-zinc-100">{{ $inspection->requested_for_time ?: 'Open time' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Handler</p>
                                        <p class="mt-1 break-words text-zinc-900 dark:text-zinc-100">{{ $inspection->handler?->name ?? 'Unassigned' }}</p>
                                    </div>
                                </div>
                            </section>
                        </div>

                        @if ($inspection->message)
                            <section class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4 dark:border-cyan-500/20 dark:bg-cyan-500/10">
                                <h3 class="text-sm font-semibold text-cyan-950 dark:text-cyan-100">Requester message</h3>
                                <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-cyan-900 dark:text-cyan-100/90">{{ $inspection->message }}</p>
                            </section>
                        @endif
                    </div>

                    <form wire:submit="save({{ $inspection->id }})" class="border-t border-zinc-200 bg-zinc-50 p-5 dark:border-white/10 dark:bg-white/5 xl:border-l xl:border-t-0">
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">Update request</h3>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Change the workflow status, schedule a visit, or add internal notes.</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Status</label>
                                <select wire:model="statusUpdates.{{ $inspection->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                                    @foreach ($statuses as $statusOption)
                                        <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Scheduled at</label>
                                <input type="datetime-local" wire:model="scheduledAts.{{ $inspection->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Internal notes</label>
                            <textarea rows="4" wire:model="internalNotes.{{ $inspection->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white"></textarea>
                        </div>
                        <button type="submit" wire:loading.attr="disabled" class="mt-4 rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">Save update</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-[1.75rem] border border-dashed border-zinc-300 bg-white/70 p-8 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">No inspection requests yet.</div>
        @endforelse
    </div>

    <div>{{ $inspections->links() }}</div>
</div>
