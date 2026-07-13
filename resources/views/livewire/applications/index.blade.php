<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Leasing</p>
            <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Rental Applications</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Review public applications, inspect supporting documents, and push each applicant through the decision queue.</p>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Search</label>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Applicant, email, property" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Status filter</label>
            <select wire:model.live="status" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
                <option value="">All statuses</option>
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>
            </div>
            <div class="sm:col-span-2">
                <a href="{{ route('exports.show', ['type' => 'applications', 'q' => $search, 'status' => $status]) }}" class="inline-flex rounded-full border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-900 transition hover:border-zinc-950 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/30">Export CSV</a>
                <a href="{{ route('reports.premium', ['type' => 'applications', 'q' => $search, 'status' => $status]) }}" target="_blank" class="ml-2 inline-flex rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">Premium PDF</a>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        @forelse ($applications as $application)
            <article class="overflow-hidden rounded-[1.25rem] border border-zinc-200 bg-white shadow-sm dark:border-white/10 dark:bg-zinc-950/60">
                <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-white/10 dark:bg-white/5">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Rental application</p>
                            <h2 class="mt-1 break-words text-lg font-semibold text-zinc-950 dark:text-white">{{ $application->property->title }}</h2>
                            <p class="mt-1 break-words text-sm text-zinc-600 dark:text-zinc-300">
                                {{ $application->unit?->unit_name ?? 'Unit not specified' }} - {{ $application->unit?->unit_type ?? 'Listing unit' }}
                            </p>
                        </div>

                        <span class="inline-flex w-fit shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $application->status->badgeClasses() }}">{{ $application->status->label() }}</span>
                    </div>
                </div>

                <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_420px]">
                    <div class="min-w-0 space-y-5 p-5">
                        <div class="grid gap-4 md:grid-cols-2">
                            <section class="min-w-0 rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">Applicant</h3>
                                <div class="mt-3 space-y-3 text-sm">
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Name</p>
                                        <p class="mt-1 break-words text-zinc-900 dark:text-zinc-100">{{ $application->applicant_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Email</p>
                                        <p class="mt-1 break-all text-zinc-900 dark:text-zinc-100">{{ $application->applicant_email }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Phone</p>
                                        <p class="mt-1 break-words text-zinc-900 dark:text-zinc-100">{{ $application->applicant_phone ?: 'Not provided' }}</p>
                                    </div>
                                </div>
                            </section>

                            <section class="min-w-0 rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">Leasing Details</h3>
                                <div class="mt-3 space-y-3 text-sm">
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Monthly income</p>
                                        <p class="mt-1 text-zinc-900 dark:text-zinc-100">{{ $application->monthly_income ? 'NGN '.number_format((float) $application->monthly_income) : 'Not provided' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Preferred move-in</p>
                                        <p class="mt-1 text-zinc-900 dark:text-zinc-100">{{ $application->preferred_move_in_date?->format('M j, Y') ?? 'Flexible' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Submitted</p>
                                        <p class="mt-1 text-zinc-900 dark:text-zinc-100">{{ $application->submitted_at?->format('M j, Y g:i A') ?? 'Not recorded' }}</p>
                                    </div>
                                </div>
                            </section>
                        </div>

                        @if ($application->message)
                            <section class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4 dark:border-cyan-500/20 dark:bg-cyan-500/10">
                                <h3 class="text-sm font-semibold text-cyan-950 dark:text-cyan-100">Applicant message</h3>
                                <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-cyan-900 dark:text-cyan-100/90">{{ $application->message }}</p>
                            </section>
                        @endif

                        @if ($application->getMedia('documents')->isNotEmpty())
                            <section class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">Supporting documents</h3>
                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    @foreach ($application->getMedia('documents') as $document)
                                        <a href="{{ $document->getUrl() }}" target="_blank" rel="noreferrer" class="min-w-0 rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-medium text-cyan-700 transition hover:border-cyan-300 hover:text-cyan-900 dark:border-white/10 dark:bg-white/5 dark:text-cyan-300 dark:hover:border-cyan-400/50">
                                            <span class="block truncate">{{ $document->name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <x-activity-timeline :logs="$application->activityLogs" empty="No application activity has been recorded yet." />
                    </div>

                    <form wire:submit="save({{ $application->id }})" class="border-t border-zinc-200 bg-zinc-50 p-5 dark:border-white/10 dark:bg-white/5 xl:border-l xl:border-t-0">
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">Review application</h3>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Update the decision status and leave review notes for the applicant record.</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Status</label>
                            <select wire:model="statusUpdates.{{ $application->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                                @foreach ($statuses as $statusOption)
                                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Review notes</label>
                            <textarea rows="5" wire:model="reviewNotes.{{ $application->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white"></textarea>
                        </div>
                        <button type="submit" wire:loading.attr="disabled" class="mt-4 rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">Save review</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-[1.75rem] border border-dashed border-zinc-300 bg-white/70 p-8 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">No rental applications yet.</div>
        @endforelse
    </div>

    <div>{{ $applications->links() }}</div>
</div>
