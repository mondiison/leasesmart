    <div class="space-y-6">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" wire:navigate class="font-medium text-zinc-700 transition hover:text-cyan-700 dark:text-zinc-300 dark:hover:text-cyan-200">Dashboard</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('properties.index') }}" wire:navigate class="font-medium text-zinc-700 transition hover:text-cyan-700 dark:text-zinc-300 dark:hover:text-cyan-200">Properties</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('properties.edit', $property) }}" wire:navigate class="min-w-0 break-words font-medium text-zinc-700 transition hover:text-cyan-700 dark:text-zinc-300 dark:hover:text-cyan-200">{{ $property->title }}</a>
            <span aria-hidden="true">/</span>
            <span class="min-w-0 break-words font-medium text-zinc-950 dark:text-white" aria-current="page">{{ $unitRecord ? $unitRecord->unit_name : 'New Unit' }}</span>
        </nav>

        <div>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Inventory</p>
            <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $unitRecord ? 'Edit '.$unitRecord->unit_name : 'New Unit for '.$property->title }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Define the rentable unit, its pricing model, and listing metadata in one stateful form.</p>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Unit Basics</h2>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Unit Name</label><input type="text" wire:model.live.debounce.300ms="unit_name" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('unit_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Unit Code</label><input type="text" wire:model.live.debounce.300ms="unit_code" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('unit_code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Unit Type</label><input type="text" wire:model.blur="unit_type" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Floor Label</label><input type="text" wire:model.blur="floor_label" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Occupancy Status</label><select wire:model.live="occupancy_status" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@foreach ($occupancyStatuses as $status)<option value="{{ $status->value }}">{{ $status->label() }}</option>@endforeach</select>@error('occupancy_status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Available From</label><input type="date" wire:model.blur="available_from" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('available_from') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        <div class="md:col-span-2"><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Description</label><textarea wire:model.blur="description" rows="4" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></textarea></div>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Commercials and Media</h2>
                    <div class="mt-5 grid gap-5">
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Billing Cycle</label><select wire:model.live="billing_cycle" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@foreach ($billingCycles as $cycle)<option value="{{ $cycle->value }}">{{ $cycle->label() }}</option>@endforeach</select>@error('billing_cycle') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Rent Amount</label><input type="number" step="0.01" wire:model.live.debounce.300ms="rent_amount" required class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@error('rent_amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror</div>
                        <div>
                            <flux:file-upload wire:model="media" multiple label="Gallery Images" description="Upload unit photos up to 5MB each.">
                                <flux:file-upload.dropzone
                                    heading="Drop unit images here or click to browse"
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
                        <label class="inline-flex items-center gap-3 rounded-2xl border border-zinc-200 px-4 py-3 text-sm dark:border-white/10"><input type="checkbox" wire:model="is_listed"><span>Show this unit on public listings when available</span></label>
                    </div>
                </section>
            </div>

            <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Layout and Charges</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Bedrooms</label><input type="number" wire:model.blur="bedrooms" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Bathrooms</label><input type="number" wire:model.blur="bathrooms" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Toilets</label><input type="number" wire:model.blur="toilets" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Size (sqm)</label><input type="number" step="0.01" wire:model.blur="size_sqm" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Service Charge</label><input type="number" step="0.01" wire:model.blur="service_charge_amount" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Caution Fee</label><input type="number" step="0.01" wire:model.blur="caution_fee_amount" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                    <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Inspection Fee</label><input type="number" step="0.01" wire:model.blur="inspection_fee_amount" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
                </div>

                <div class="mt-6">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Amenities</label>
                    <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        @forelse ($amenities as $amenity)
                            <label class="inline-flex items-center gap-3 rounded-2xl border border-zinc-200 px-4 py-3 text-sm dark:border-white/10"><input type="checkbox" wire:model="amenity_ids" value="{{ $amenity->id }}"><span>{{ $amenity->name }}</span></label>
                        @empty
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">No amenities seeded yet.</p>
                        @endforelse
                    </div>
                </div>

                @if ($unitRecord && $unitRecord->media->isNotEmpty())
                    <div class="mt-8">
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Current Gallery</h3>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">The first image is used as this unit's cover where unit photos are shown.</p>
                        </div>

                        <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ($unitRecord->media->sortBy('order_column')->values() as $index => $item)
                                <article class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-white/10 dark:bg-zinc-950/60">
                                    <div class="relative">
                                        <img src="{{ $item->getUrl() }}" alt="{{ $unitRecord->unit_name }} image" class="h-36 w-full object-cover">
                                        @if ($index === 0)
                                            <span class="absolute left-3 top-3 rounded-full bg-zinc-950 px-3 py-1 text-xs font-semibold text-white dark:bg-white dark:text-zinc-950">Cover</span>
                                        @endif
                                    </div>
                                    <div class="flex flex-col gap-2 p-3">
                                        @if ($index !== 0)
                                            <button type="button" wire:click="setGalleryCover({{ $item->id }})" class="rounded-full border border-zinc-300 px-3 py-2 text-xs font-semibold text-zinc-800 transition hover:border-cyan-400 hover:text-cyan-700 dark:border-white/10 dark:text-zinc-200 dark:hover:border-cyan-300/50 dark:hover:text-cyan-200">Make cover</button>
                                        @endif
                                        <button type="button" wire:click="deleteGalleryImage({{ $item->id }})" wire:confirm="Remove this unit image?" class="rounded-full border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:border-rose-400 dark:border-rose-400/20 dark:text-rose-300">Remove</button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6"><button type="submit" @disabled(! $this->canSubmit) wire:loading.attr="disabled" class="rounded-full bg-zinc-950 px-5 py-2.5 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">{{ $unitRecord ? 'Save Unit' : 'Create Unit' }}</button></div>
            </section>
        </form>
    </div>
