    <div class="space-y-6">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" wire:navigate class="font-medium text-zinc-700 transition hover:text-cyan-700 dark:text-zinc-300 dark:hover:text-cyan-200">Dashboard</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('properties.index') }}" wire:navigate class="font-medium text-zinc-700 transition hover:text-cyan-700 dark:text-zinc-300 dark:hover:text-cyan-200">Properties</a>
            <span aria-hidden="true">/</span>
            <span class="min-w-0 break-words font-medium text-zinc-950 dark:text-white" aria-current="page">{{ $propertyRecord?->title ?: 'Create Property' }}</span>
        </nav>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Inventory</p>
                <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $propertyRecord?->title ?: 'Create Property' }}</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Manage the property record, owner assignment, media, and publish readiness from one stateful workspace.</p>
            </div>
            @if ($propertyRecord)
                <a href="{{ route('properties.units.create', $propertyRecord) }}" wire:navigate class="rounded-full border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 dark:border-white/10 dark:text-white">Add Unit</a>
            @endif
        </div>

        @if ($errors->has('publish_status'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-200">{{ $errors->first('publish_status') }}</div>
        @endif

        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Property Identity</h2>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Title</label>
                            <input type="text" wire:model.live.debounce.300ms="title" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                            @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Property Code</label>
                            <input type="text" wire:model.live.debounce.300ms="property_code" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                            @error('property_code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Property Type</label>
                            <select wire:model.live="property_type" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                                @foreach ($propertyTypes as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                            @error('property_type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Publish Status</label>
                            <select wire:model.live="publish_status" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                                @foreach ($publishStatuses as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            @error('publish_status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Description</label>
                            <textarea wire:model.blur="description" rows="5" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></textarea>
                        </div>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Assignments and Flags</h2>
                    <div class="mt-5 grid gap-5">
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Landlord</label>
                            <select wire:model="landlord_id" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                                <option value="">Select landlord</option>
                                @foreach ($landlords as $landlord)
                                    <option value="{{ $landlord->id }}">{{ $landlord->user?->name ?? 'Landlord #'.$landlord->id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Caretaker</label>
                            <select wire:model="caretaker_id" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                                <option value="">Select caretaker</option>
                                @foreach ($caretakers as $caretaker)
                                    <option value="{{ $caretaker->id }}">{{ $caretaker->user?->name ?? 'Caretaker #'.$caretaker->id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="inline-flex items-center gap-3 rounded-2xl border border-zinc-200 px-4 py-3 text-sm dark:border-white/10">
                            <input type="checkbox" wire:model="is_featured">
                            <span>Feature this property in future listing surfaces</span>
                        </label>
                        <div>
                            <flux:file-upload wire:model="media" multiple label="Gallery Images" description="Upload JPG or PNG images up to 5MB each.">
                                <flux:file-upload.dropzone
                                    heading="Drop property images here or click to browse"
                                    text="JPG, PNG up to 5MB"
                                    with-progress
                                    inline
                                />
                            </flux:file-upload>
                            @error('media.*') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror

                            @if ($media !== [])
                                <div class="mt-3 flex flex-col gap-2">
                                    @foreach ($media as $index => $file)
                                        <flux:file-item
                                            :heading="$file->getClientOriginalName()"
                                            :image="$file->temporaryUrl()"
                                            :size="$file->getSize()"
                                        >
                                            <x-slot name="actions">
                                                <flux:file-item.remove wire:click="removeMediaUpload({{ $index }})" aria-label="Remove {{ $file->getClientOriginalName() }}" />
                                            </x-slot>
                                        </flux:file-item>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            </div>

            <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Location and Amenities</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Address Line 1</label><input type="text" wire:model.live.debounce.300ms="address_line_1" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('address_line_1') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Address Line 2</label><input type="text" wire:model.blur="address_line_2" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">City</label><input type="text" wire:model.live.debounce.300ms="city" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('city') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">State</label><input type="text" wire:model.blur="state" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Country</label><input type="text" wire:model.live.debounce.300ms="country" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('country') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Postal Code</label><input type="text" wire:model.blur="postal_code" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Latitude</label><input type="number" step="0.0000001" wire:model.blur="latitude" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('latitude') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Longitude</label><input type="number" step="0.0000001" wire:model.blur="longitude" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('longitude') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Year Built</label><input type="number" wire:model.blur="year_built" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('year_built') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                </div>

                <div class="mt-6">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Amenities</label>
                    <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        @forelse ($amenities as $amenity)
                            <label class="inline-flex items-center gap-3 rounded-2xl border border-zinc-200 px-4 py-3 text-sm dark:border-white/10">
                                <input type="checkbox" wire:model="amenity_ids" value="{{ $amenity->id }}">
                                <span>{{ $amenity->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">No amenities seeded yet.</p>
                        @endforelse
                    </div>
                </div>

                @if ($propertyRecord && $propertyRecord->media->isNotEmpty())
                    <div class="mt-8">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Current Gallery</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">The first image is used as the public listing cover.</p>
                            </div>
                        </div>
                        <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ($propertyRecord->media->sortBy('order_column')->values() as $index => $item)
                                <article class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-white/10 dark:bg-zinc-950/60">
                                    <div class="relative">
                                        <img src="{{ $item->getUrl() }}" alt="{{ $propertyRecord->title }} image" class="h-40 w-full object-cover">
                                        @if ($index === 0)
                                            <span class="absolute left-3 top-3 rounded-full bg-zinc-950 px-3 py-1 text-xs font-semibold text-white dark:bg-white dark:text-zinc-950">Cover</span>
                                        @endif
                                    </div>
                                    <div class="flex flex-col gap-2 p-3">
                                        @if ($index !== 0)
                                            <button type="button" wire:click="setGalleryCover({{ $item->id }})" class="rounded-full border border-zinc-300 px-3 py-2 text-xs font-semibold text-zinc-800 transition hover:border-cyan-400 hover:text-cyan-700 dark:border-white/10 dark:text-zinc-200 dark:hover:border-cyan-300/50 dark:hover:text-cyan-200">Make cover</button>
                                        @endif
                                        <button type="button" wire:click="deleteGalleryImage({{ $item->id }})" wire:confirm="Remove this property image?" class="rounded-full border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:border-rose-400 dark:border-rose-400/20 dark:text-rose-300">Remove</button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button type="submit" @disabled(! $this->canSubmit) wire:loading.attr="disabled" class="rounded-full bg-zinc-950 px-5 py-2.5 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">{{ $propertyRecord ? 'Save Changes' : 'Create Property' }}</button>
                    @if ($propertyRecord)
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">Slug: {{ $propertyRecord->slug }}</span>
                    @endif
                </div>
            </section>
        </form>

        @if ($propertyRecord)
            <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Publish Workflow</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Move the property through review, live listing readiness, and archive states.</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $propertyRecord->publish_status->badgeClasses() }}">{{ $propertyRecord->publish_status->label() }}</span>
                </div>
                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach ($publishStatuses as $status)
                        <button type="button" wire:click="updatePublishStatus('{{ $status->value }}')" class="rounded-full border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-900 dark:border-white/10 dark:text-white">{{ $status->label() }}</button>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Units</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Track commercial terms, occupancy, and listing readiness at the unit level.</p>
                    </div>
                    <a href="{{ route('properties.units.create', $propertyRecord) }}" wire:navigate class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">New Unit</a>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    @forelse ($propertyRecord->units as $unit)
                        <article class="rounded-3xl border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-zinc-950 dark:text-white">{{ $unit->unit_name }}</h3>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $unit->unit_code ?: 'No unit code yet' }}</p>
                                </div>
                                <span class="rounded-full bg-zinc-950/5 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-white/10 dark:text-zinc-200">{{ $unit->occupancy_status->label() }}</span>
                            </div>
                            <div class="mt-4 grid gap-3 text-sm text-zinc-700 dark:text-zinc-300 sm:grid-cols-2">
                                <p>Rent: NGN {{ number_format((float) $unit->rent_amount, 2) }}</p>
                                <p>Billing: {{ $unit->billing_cycle->label() }}</p>
                                <p>Listed: {{ $unit->is_listed ? 'Yes' : 'No' }}</p>
                                <p>Available: {{ $unit->available_from?->format('M j, Y') ?? 'On request' }}</p>
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-3">
                                <p class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ $unit->amenities->count() }} amenities</p>
                                <a href="{{ route('properties.units.edit', [$propertyRecord, $unit]) }}" wire:navigate class="text-sm font-medium text-cyan-700 dark:text-cyan-300">Edit unit</a>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-3xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-white/10 dark:text-zinc-300 lg:col-span-2">No units yet. Add one listed unit before publishing this property live.</div>
                    @endforelse
                </div>
            </section>
        @endif
    </div>
