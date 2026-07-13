<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Occupancy</p>
            <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $tenantPortal ? 'My Tenancy' : 'Tenancies' }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ $tenantPortal ? 'Track your home, rent, payment progress, lease dates, and support requests from one place.' : 'Track lease lifecycles, convert approved applications, and keep unit occupancy aligned with real tenancy status.' }}
            </p>
        </div>
        @unless ($tenantPortal)
            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Search</label>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Tenant, property, unit" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
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
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Focus</label>
                    <select wire:model.live="focus" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
                        <option value="">All tenancies</option>
                        <option value="expiring">Expiring in 60 days</option>
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <a href="{{ route('exports.show', ['type' => 'tenancies', 'q' => $search, 'status' => $status, 'focus' => $focus]) }}" class="inline-flex rounded-full border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-900 transition hover:border-zinc-950 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/30">Export CSV</a>
                    <a href="{{ route('reports.premium', ['type' => 'tenancies', 'q' => $search, 'status' => $status, 'focus' => $focus]) }}" target="_blank" class="ml-2 inline-flex rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">Premium PDF</a>
                </div>
            </div>
        @endunless
    </div>

    @if ($tenantPortal)
        <section class="overflow-hidden rounded-[1.25rem] border border-zinc-200 bg-white shadow-sm dark:border-white/10 dark:bg-zinc-950/60">
            <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-5 dark:border-white/10 dark:bg-white/5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Tenant portal</p>
                        <h2 class="mt-1 break-words text-xl font-semibold text-zinc-950 dark:text-white">
                            {{ $tenantPortal['activeTenancy']?->property?->title ?? 'No active tenancy yet' }}
                        </h2>
                        <p class="mt-1 break-words text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $tenantPortal['activeTenancy']?->unit?->unit_name ?? 'Unit pending allocation' }}
                        </p>
                    </div>

                    @if ($tenantPortal['activeTenancy'])
                        <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $tenantPortal['activeTenancy']->status->badgeClasses() }}">
                            {{ $tenantPortal['activeTenancy']->status->label() }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                    <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Outstanding balance</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">NGN {{ number_format((float) $tenantPortal['outstandingBalance'], 2) }}</p>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $tenantPortal['openInvoices'] }} open invoice(s)</p>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                    <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Payments</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ $tenantPortal['pendingPayments'] }}</p>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $tenantPortal['verifiedPayments'] }} verified payment(s)</p>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                    <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Lease expiry</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                        {{ $tenantPortal['leaseDaysRemaining'] === null ? 'Open' : number_format($tenantPortal['leaseDaysRemaining']).' days' }}
                    </p>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $tenantPortal['activeTenancy']?->lease_end_date?->format('M j, Y') ?? 'No end date recorded' }}</p>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                    <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Support requests</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ $tenantPortal['openMaintenance'] }}</p>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Open maintenance item(s)</p>
                </div>
            </div>

            @if ($tenantPortal['activeTenancy'])
                <div class="border-t border-zinc-200 p-5 dark:border-white/10">
                    <section class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">Lease documents</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $tenantPortal['activeTenancy']->getMedia('documents')->count() }} document(s) on this tenancy.</p>
                            </div>
                            <a href="{{ route('notifications.index') }}" wire:navigate class="text-sm font-medium text-cyan-700 dark:text-cyan-300">Notification inbox</a>
                        </div>

                        <div class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                            @forelse ($tenantPortal['activeTenancy']->getMedia('documents') as $document)
                                <a href="{{ route('tenancies.documents.show', $document) }}" target="_blank" rel="noreferrer" class="min-w-0 rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-medium text-cyan-700 transition hover:border-cyan-300 hover:text-cyan-900 dark:border-white/10 dark:bg-white/5 dark:text-cyan-300 dark:hover:border-cyan-400/50">
                                    <span class="block truncate">{{ $document->name }}</span>
                                    <span class="mt-1 block text-zinc-500 dark:text-zinc-400">{{ strtoupper($document->extension) }} - {{ number_format($document->size / 1024, 1) }} KB</span>
                                </a>
                            @empty
                                <div class="rounded-2xl border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-white/10 dark:text-zinc-300">No lease documents have been uploaded yet.</div>
                            @endforelse
                        </div>
                    </section>
                </div>
            @endif

            <div class="grid gap-5 border-t border-zinc-200 p-5 dark:border-white/10 xl:grid-cols-2">
                <section class="min-w-0">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">Recent invoices</h3>
                        <a href="{{ route('billing.index') }}" wire:navigate class="text-sm font-medium text-cyan-700 hover:text-cyan-900 dark:text-cyan-300 dark:hover:text-cyan-200">Open billing</a>
                    </div>
                    <div class="mt-3 space-y-2">
                        @forelse ($tenantPortal['latestInvoices'] as $invoice)
                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 text-sm dark:border-white/10 dark:bg-white/5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-medium text-zinc-950 dark:text-white">{{ $invoice->invoice_number }}</p>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $invoice->status->badgeClasses() }}">{{ $invoice->status->label() }}</span>
                                </div>
                                <p class="mt-2 text-zinc-600 dark:text-zinc-300">Balance: NGN {{ number_format((float) $invoice->balance_amount, 2) }}</p>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Due {{ $invoice->due_date->format('M j, Y') }}</p>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-white/10 dark:text-zinc-300">No invoices yet.</div>
                        @endforelse
                    </div>
                </section>

                <section class="min-w-0">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-zinc-950 dark:text-white">Recent support</h3>
                        <a href="{{ route('maintenance.index') }}" wire:navigate class="text-sm font-medium text-cyan-700 hover:text-cyan-900 dark:text-cyan-300 dark:hover:text-cyan-200">Open support</a>
                    </div>
                    <div class="mt-3 space-y-2">
                        @forelse ($tenantPortal['latestMaintenance'] as $request)
                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 text-sm dark:border-white/10 dark:bg-white/5">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="break-words font-medium text-zinc-950 dark:text-white">{{ $request->title }}</p>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $request->status->badgeClasses() }}">{{ $request->status->label() }}</span>
                                </div>
                                <p class="mt-2 break-words text-zinc-600 dark:text-zinc-300">{{ $request->property?->title }}{{ $request->unit ? ' - '.$request->unit->unit_name : '' }}</p>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $request->created_at->diffForHumans() }}</p>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-white/10 dark:text-zinc-300">No support requests yet.</div>
                        @endforelse
                    </div>
                </section>
            </div>

            @if ($tenantPortal['activeTenancy'])
                <div class="border-t border-zinc-200 p-5 dark:border-white/10">
                    <x-activity-timeline :logs="$tenantPortal['activeTenancy']->activityLogs" empty="No tenancy activity has been recorded yet." />
                </div>
            @endif
        </section>
    @endif

    @if ($convertibleApplications->isNotEmpty())
        <section class="space-y-4">
            <div>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Approved Applications Ready For Conversion</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Create tenancies directly from approved applications so occupancy and application lifecycle stay in sync.</p>
            </div>

            <div class="space-y-4">
                @foreach ($convertibleApplications as $application)
                    <article class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ $application->property->title }}</h3>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $application->status->badgeClasses() }}">{{ $application->status->label() }}</span>
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $application->unit?->unit_name ?? 'Unit' }} - {{ $application->applicant_name }}</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Preferred move-in: {{ $application->preferred_move_in_date?->format('M j, Y') ?? 'Flexible' }}</p>
                            </div>

                            <form wire:submit="createFromApplication({{ $application->id }})" class="w-full max-w-3xl space-y-4 rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50 p-5 dark:border-white/10 dark:bg-zinc-950/40">
                                <div class="grid gap-4 md:grid-cols-3">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Status</label>
                                        <select wire:model="createStatus.{{ $application->id }}" required class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                                            @foreach ($statuses as $statusOption)
                                                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Lease start</label>
                                        <input type="date" wire:model.live="createLeaseStartDates.{{ $application->id }}" required class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Lease end</label>
                                        <input type="date" wire:model="createLeaseEndDates.{{ $application->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Move-in</label>
                                        <input type="date" wire:model="createMoveInDates.{{ $application->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Rent</label>
                                        <input type="number" step="0.01" wire:model.live.debounce.300ms="createRentAmounts.{{ $application->id }}" required class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Service charge</label>
                                        <input type="number" step="0.01" wire:model="createServiceCharges.{{ $application->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Billing</label>
                                        <select wire:model.live="createBillingCycles.{{ $application->id }}" required class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                                            @foreach ($billingCycles as $billingCycle)
                                                <option value="{{ $billingCycle->value }}">{{ $billingCycle->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Notes</label>
                                    <textarea rows="3" wire:model="createNotes.{{ $application->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white"></textarea>
                                </div>

                                @error("createTenancy.$application->id") <p class="text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
                                <button type="submit" @disabled(! $this->canCreateTenancy($application->id)) wire:loading.attr="disabled" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">Create tenancy</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="space-y-4">
        <div>
            <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Active Tenancy Register</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Manage lifecycle status and commercial terms for current and historical occupancies.</p>
        </div>

        <div class="space-y-4">
            @forelse ($tenancies as $tenancy)
                <article class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <div class="space-y-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ $tenancy->property->title }}</h3>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $tenancy->status->badgeClasses() }}">{{ $tenancy->status->label() }}</span>
                            </div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $tenancy->unit->unit_name }} - {{ $tenancy->tenant_name }}</p>
                            <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                <div><dt class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Lease window</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $tenancy->lease_start_date?->format('M j, Y') }}{{ $tenancy->lease_end_date ? ' to '.$tenancy->lease_end_date->format('M j, Y') : '' }}</dd></div>
                                <div><dt class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Move-in</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $tenancy->move_in_date?->format('M j, Y') ?? 'Not set' }}</dd></div>
                                <div><dt class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Rent</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">NGN {{ number_format((float) $tenancy->rent_amount) }}</dd></div>
                                <div><dt class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Billing</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $tenancy->billing_cycle->label() }}</dd></div>
                            </dl>

                            <section class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-zinc-950 dark:text-white">Tenancy documents</h4>
                                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $tenancy->getMedia('documents')->count() }} file(s) attached to this record.</p>
                                    </div>

                                    @can('update', $tenancy)
                                        <form wire:submit="uploadDocuments({{ $tenancy->id }})" class="w-full space-y-3 md:max-w-sm">
                                            <input type="file" wire:model="documents.{{ $tenancy->id }}" multiple class="block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" />
                                            @error('documents.'.$tenancy->id) <p class="text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
                                            @error('documents.'.$tenancy->id.'.*') <p class="text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
                                            <button type="submit" wire:loading.attr="disabled" wire:target="documents.{{ $tenancy->id }},uploadDocuments({{ $tenancy->id }})" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">Upload documents</button>
                                        </form>
                                    @endcan
                                </div>

                                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                    @forelse ($tenancy->getMedia('documents') as $document)
                                        <div class="min-w-0 rounded-2xl border border-zinc-200 bg-zinc-50 p-3 dark:border-white/10 dark:bg-white/5">
                                            <a href="{{ route('tenancies.documents.show', $document) }}" target="_blank" rel="noreferrer" class="block text-xs font-medium text-cyan-700 transition hover:text-cyan-900 dark:text-cyan-300">
                                                <span class="block truncate">{{ $document->name }}</span>
                                                <span class="mt-1 block text-zinc-500 dark:text-zinc-400">{{ strtoupper($document->extension) }} - {{ number_format($document->size / 1024, 1) }} KB</span>
                                            </a>

                                            @can('update', $tenancy)
                                                <div class="mt-3 space-y-2">
                                                    <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-300">Document label</label>
                                                    <input type="text" wire:model="documentLabels.{{ $document->id }}" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <button type="button" wire:click="renameDocument({{ $document->id }})" wire:loading.attr="disabled" class="rounded-full border border-zinc-300 bg-white px-3 py-1.5 text-xs font-medium text-zinc-800 disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:bg-white/5 dark:text-white">Save label</button>
                                                        <button type="button" wire:click="deleteDocument({{ $document->id }})" wire:confirm="Delete this tenancy document?" wire:loading.attr="disabled" class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 disabled:cursor-not-allowed disabled:opacity-50 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200">Delete</button>
                                                    </div>
                                                </div>
                                            @endcan
                                        </div>
                                    @empty
                                        <div class="rounded-2xl border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-white/10 dark:text-zinc-300">No tenancy documents yet.</div>
                                    @endforelse
                                </div>
                            </section>

                            <x-activity-timeline :logs="$tenancy->activityLogs" empty="No tenancy activity has been recorded yet." />
                        </div>

                        @can('update', $tenancy)
                            <form wire:submit="saveTenancy({{ $tenancy->id }})" class="w-full max-w-3xl space-y-4 rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50 p-5 dark:border-white/10 dark:bg-zinc-950/40">
                                <div class="grid gap-4 md:grid-cols-3">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Status</label>
                                        <select wire:model="updateStatus.{{ $tenancy->id }}" required class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                                            @foreach ($statuses as $statusOption)
                                                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Lease start</label>
                                        <input type="date" wire:model.live="updateLeaseStartDates.{{ $tenancy->id }}" required class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Lease end</label>
                                        <input type="date" wire:model="updateLeaseEndDates.{{ $tenancy->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Move-in</label>
                                        <input type="date" wire:model="updateMoveInDates.{{ $tenancy->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Rent</label>
                                        <input type="number" step="0.01" wire:model.live.debounce.300ms="updateRentAmounts.{{ $tenancy->id }}" required class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Service charge</label>
                                        <input type="number" step="0.01" wire:model="updateServiceCharges.{{ $tenancy->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Billing</label>
                                        <select wire:model.live="updateBillingCycles.{{ $tenancy->id }}" required class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                                            @foreach ($billingCycles as $billingCycle)
                                                <option value="{{ $billingCycle->value }}">{{ $billingCycle->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Notes</label>
                                    <textarea rows="3" wire:model="updateNotes.{{ $tenancy->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white"></textarea>
                                </div>

                                <button type="submit" @disabled(! $this->canUpdateTenancy($tenancy->id)) wire:loading.attr="disabled" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">Save tenancy</button>
                            </form>
                        @else
                            <div class="rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50 p-5 text-sm text-zinc-600 dark:border-white/10 dark:bg-zinc-950/40 dark:text-zinc-300">
                                You have view-only access to this tenancy record.
                            </div>
                        @endcan
                    </div>
                </article>
            @empty
                <div class="rounded-[1.75rem] border border-dashed border-zinc-300 bg-white/70 p-8 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">No tenancies yet.</div>
            @endforelse
        </div>

        <div>{{ $tenancies->links() }}</div>
    </section>
</div>
