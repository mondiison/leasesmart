<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $isTenant ? 'Support Center' : 'Operations' }}</p>
            <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $isTenant ? 'Maintenance Support' : 'Maintenance' }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ $isTenant ? 'Report an issue, attach evidence, and track every update until it is resolved.' : 'Track resident issues, assignments, attachments, and resolution timelines from one workspace.' }}
            </p>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-zinc-500 dark:text-zinc-400">Requests</p>
                <p class="mt-1 text-xl font-semibold text-zinc-950 dark:text-white">{{ $requests->count() }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-zinc-500 dark:text-zinc-400">Open</p>
                <p class="mt-1 text-xl font-semibold text-zinc-950 dark:text-white">{{ $requests->whereIn('status.value', ['open', 'assigned', 'in_progress', 'awaiting_confirmation'])->count() }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-zinc-500 dark:text-zinc-400">Resolved</p>
                <p class="mt-1 text-xl font-semibold text-zinc-950 dark:text-white">{{ $requests->whereIn('status.value', ['resolved', 'closed'])->count() }}</p>
            </div>
        </div>
    </div>

    <section class="grid gap-3 rounded-[1.25rem] border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/60 md:grid-cols-3">
        <div>
            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Search support</label>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Issue, tenant, property, unit" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Status filter</label>
            <select wire:model.live="status" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                <option value="">All statuses</option>
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Focus</label>
            <select wire:model.live="focus" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                <option value="">All requests</option>
                <option value="open">Open work</option>
                <option value="urgent">Urgent priority</option>
            </select>
        </div>
        <div class="md:col-span-3">
            <a href="{{ route('exports.show', ['type' => 'maintenance', 'q' => $search, 'status' => $status, 'focus' => $focus]) }}" class="inline-flex rounded-full border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-900 transition hover:border-zinc-950 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/30">Export CSV</a>
            <a href="{{ route('reports.premium', ['type' => 'maintenance', 'q' => $search, 'status' => $status, 'focus' => $focus]) }}" target="_blank" class="ml-2 inline-flex rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">Premium PDF</a>
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.25rem] border border-zinc-200 bg-white shadow-sm dark:border-white/10 dark:bg-zinc-950/60">
        <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-white/10 dark:bg-white/5">
            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $isTenant ? 'Report an issue' : 'New Maintenance Request' }}</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $isTenant ? 'Choose your tenancy, describe what is wrong, and upload photos or documents if helpful.' : 'Create a resident support issue with optional tenancy context and attachments.' }}</p>
        </div>

        <form wire:submit="createRequest" class="space-y-5 p-5">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $isTenant ? 'Your property' : 'Property' }}</label>
                    <select wire:model.live="property_id" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                        <option value="">Select property</option>
                        @foreach ($properties as $property)
                            <option value="{{ $property->id }}">{{ $property->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Unit</label>
                    <select wire:model="property_unit_id" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                        <option value="">Optional unit</option>
                        @foreach ($properties->firstWhere('id', $property_id)?->units ?? [] as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Tenancy</label>
                    <select wire:model.live="tenancy_id" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                        <option value="">Optional tenancy</option>
                        @foreach ($tenancies as $tenancy)
                            <option value="{{ $tenancy->id }}">{{ $tenancy->property->title }} - {{ $tenancy->tenant_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Priority</label>
                    <select wire:model.live="priority" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                        @foreach ($priorities as $priorityOption)
                            <option value="{{ $priorityOption->value }}">{{ $priorityOption->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Issue title</label>
                    <input type="text" wire:model.live.debounce.300ms="title" placeholder="Example: Kitchen sink leak" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Category</label>
                    <select wire:model="category" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                        <option value="">Select category</option>
                        @foreach ($categories as $categoryOption)
                            <option value="{{ $categoryOption }}">{{ str($categoryOption)->headline() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Description</label>
                <textarea rows="5" wire:model.live.debounce.300ms="description" placeholder="Describe the issue, where it is happening, and anything urgent." class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></textarea>
            </div>

            @unless ($isTenant)
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Assign To</label>
                    <select wire:model="assigned_to" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                        <option value="">Unassigned</option>
                        @foreach ($assignableUsers as $assignee)
                            <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div>
                <flux:file-upload wire:model="attachments" multiple label="Attachments" description="PDF, image, or document files up to 5MB each.">
                    <flux:file-upload.dropzone
                        heading="Drop evidence here or click to browse"
                        text="PDF, JPG, PNG, DOC, DOCX up to 5MB"
                        with-progress
                        inline
                    />
                </flux:file-upload>

                @if ($attachments !== [])
                    <div class="mt-3 flex flex-col gap-2">
                        @foreach ($attachments as $index => $file)
                            <flux:file-item
                                :heading="$file->getClientOriginalName()"
                                :image="str_starts_with($file->getMimeType(), 'image/') ? $file->temporaryUrl() : null"
                                :icon="str_starts_with($file->getMimeType(), 'image/') ? null : 'document'"
                                :size="$file->getSize()"
                            >
                                <x-slot name="actions">
                                    <flux:file-item.remove wire:click="removeAttachmentUpload({{ $index }})" aria-label="Remove {{ $file->getClientOriginalName() }}" />
                                </x-slot>
                            </flux:file-item>
                        @endforeach
                    </div>
                @endif
            </div>

            <button type="submit" @disabled(! $this->canSubmit) wire:loading.attr="disabled" class="rounded-full bg-zinc-950 px-5 py-2.5 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">
                {{ $isTenant ? 'Submit support request' : 'Create request' }}
            </button>
        </form>
    </section>

    <section class="space-y-4">
        <div>
            <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ $isTenant ? 'My Support Requests' : 'Maintenance Queue' }}</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $isTenant ? 'Follow status changes, notes, and resolution confirmation for every request.' : 'Review issue context, attachments, assignments, and timeline updates in one place.' }}</p>
        </div>

        <div class="space-y-4">
            @forelse ($requests as $request)
                <article class="overflow-hidden rounded-[1.25rem] border border-zinc-200 bg-white shadow-sm dark:border-white/10 dark:bg-zinc-950/60">
                    <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-white/10 dark:bg-white/5">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">{{ $request->category ? str($request->category)->headline() : 'General issue' }}</p>
                                <h3 class="mt-1 break-words text-lg font-semibold text-zinc-950 dark:text-white">{{ $request->title }}</h3>
                                <p class="mt-1 break-words text-sm text-zinc-600 dark:text-zinc-300">{{ $request->property->title }}{{ $request->unit ? ' - '.$request->unit->unit_name : '' }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $request->status->badgeClasses() }}">{{ $request->status->label() }}</span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $request->priority->badgeClasses() }}">{{ $request->priority->label() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_420px]">
                        <div class="min-w-0 space-y-5 p-5">
                            <section class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                <h4 class="text-sm font-semibold text-zinc-950 dark:text-white">Issue summary</h4>
                                <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-zinc-700 dark:text-zinc-200">{{ $request->description }}</p>
                                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2 xl:grid-cols-4">
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Tenant</p>
                                        <p class="mt-1 break-words text-zinc-900 dark:text-zinc-100">{{ $request->tenantUser?->name ?? 'Internal request' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Assigned</p>
                                        <p class="mt-1 break-words text-zinc-900 dark:text-zinc-100">{{ $request->assignee?->name ?? 'Unassigned' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Reported</p>
                                        <p class="mt-1 text-zinc-900 dark:text-zinc-100">{{ $request->reported_at?->format('M j, Y g:i A') ?? 'Now' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Last update</p>
                                        <p class="mt-1 text-zinc-900 dark:text-zinc-100">{{ $request->updated_at?->diffForHumans() ?? 'Just now' }}</p>
                                    </div>
                                </div>
                            </section>

                            @if ($request->getMedia('attachments')->isNotEmpty())
                                <section class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                    <h4 class="text-sm font-semibold text-zinc-950 dark:text-white">Attachments</h4>
                                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                        @foreach ($request->getMedia('attachments') as $attachment)
                                            <a href="{{ $attachment->getUrl() }}" target="_blank" rel="noreferrer" class="min-w-0 rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-medium text-cyan-700 transition hover:border-cyan-300 hover:text-cyan-900 dark:border-white/10 dark:bg-white/5 dark:text-cyan-300">
                                                <span class="block truncate">{{ $attachment->name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </section>
                            @endif

                            <section class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                <h4 class="text-sm font-semibold text-zinc-950 dark:text-white">Timeline</h4>
                                <div class="mt-4 space-y-4">
                                    @foreach ($request->updates as $update)
                                        <div class="relative border-l border-zinc-200 pl-4 dark:border-white/10">
                                            <span class="absolute -left-[5px] top-1.5 h-2.5 w-2.5 rounded-full bg-cyan-600 dark:bg-cyan-300"></span>
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $update->user?->name ?? 'System update' }}</p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $update->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if ($update->status)
                                                <p class="mt-1 text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">{{ $update->status->label() }}</p>
                                            @endif
                                            @if ($update->message)
                                                <p class="mt-2 whitespace-pre-line break-words text-sm text-zinc-700 dark:text-zinc-200">{{ $update->message }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </section>

                            <x-activity-timeline
                                :logs="$request->activityLogs"
                                empty="No audit activity has been recorded for this request yet."
                            />
                        </div>

                        <aside class="border-t border-zinc-200 bg-zinc-50 p-5 dark:border-white/10 dark:bg-white/5 xl:border-l xl:border-t-0">
                            @if ($isTenant)
                                <h4 class="text-sm font-semibold text-zinc-950 dark:text-white">Next step</h4>
                                @if ($request->status === \App\Enums\MaintenanceStatus::Resolved)
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">The team marked this issue as resolved. Confirm it is fixed or reopen it for more follow-up.</p>
                                    <textarea rows="4" wire:model="tenantResolutionNotes.{{ $request->id }}" class="mt-4 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" placeholder="Optional note"></textarea>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <button type="button" wire:click="confirmResolution({{ $request->id }})" wire:loading.attr="disabled" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50">Confirm fixed</button>
                                        <button type="button" wire:click="reopenRequest({{ $request->id }})" wire:loading.attr="disabled" class="rounded-full border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:text-white">Reopen</button>
                                    </div>
                                @elseif ($request->status === \App\Enums\MaintenanceStatus::Closed)
                                    <p class="mt-1 text-sm text-emerald-700 dark:text-emerald-300">This request is closed.</p>
                                @else
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Your request is in progress. You can add a note below if there is new information.</p>
                                    @can('update', $request)
                                        <form wire:submit="addUpdate({{ $request->id }})" class="mt-4 space-y-4">
                                            <input type="hidden" wire:model="statusUpdates.{{ $request->id }}">
                                            <textarea rows="4" wire:model="messages.{{ $request->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" placeholder="Add a follow-up note"></textarea>
                                            <button type="submit" wire:loading.attr="disabled" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">Add note</button>
                                        </form>
                                    @endcan
                                @endif
                            @else
                                @can('update', $request)
                                    <h4 class="text-sm font-semibold text-zinc-950 dark:text-white">Update request</h4>
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Move the request forward, assign responsibility, and leave a timeline note.</p>
                                    <form wire:submit="addUpdate({{ $request->id }})" class="mt-4 space-y-4">
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Status</label>
                                            <select wire:model="statusUpdates.{{ $request->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                                                @foreach ($statuses as $statusOption)
                                                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Assign To</label>
                                            <select wire:model="assigneeUpdates.{{ $request->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                                                <option value="">Unassigned</option>
                                                @foreach ($assignableUsers as $assignee)
                                                    <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Update note</label>
                                            <textarea rows="4" wire:model="messages.{{ $request->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white"></textarea>
                                        </div>
                                        <div>
                                            <flux:file-upload wire:model="updateAttachments.{{ $request->id }}" multiple label="Attach files" description="Add photos or documents for this update.">
                                                <flux:file-upload.dropzone
                                                    heading="Drop files here or click to browse"
                                                    text="PDF, JPG, PNG, DOC, DOCX up to 5MB"
                                                    with-progress
                                                    inline
                                                />
                                            </flux:file-upload>

                                            @if (($updateAttachments[$request->id] ?? []) !== [])
                                                <div class="mt-3 flex flex-col gap-2">
                                                    @foreach ($updateAttachments[$request->id] as $index => $file)
                                                        <flux:file-item
                                                            :heading="$file->getClientOriginalName()"
                                                            :image="str_starts_with($file->getMimeType(), 'image/') ? $file->temporaryUrl() : null"
                                                            :icon="str_starts_with($file->getMimeType(), 'image/') ? null : 'document'"
                                                            :size="$file->getSize()"
                                                        >
                                                            <x-slot name="actions">
                                                                <flux:file-item.remove wire:click="removeUpdateAttachmentUpload({{ $request->id }}, {{ $index }})" aria-label="Remove {{ $file->getClientOriginalName() }}" />
                                                            </x-slot>
                                                        </flux:file-item>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <button type="submit" wire:loading.attr="disabled" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">Save update</button>
                                    </form>
                                @endcan
                            @endif
                        </aside>
                    </div>
                </article>
            @empty
                <div class="rounded-[1.75rem] border border-dashed border-zinc-300 bg-white/70 p-8 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">No maintenance requests yet.</div>
            @endforelse
        </div>
    </section>
</div>
