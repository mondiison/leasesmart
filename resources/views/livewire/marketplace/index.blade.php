<div class="pb-16">
    <section class="mx-auto max-w-7xl px-4 pt-10 sm:px-6 lg:px-8 {{ $isHome ? 'pb-10' : 'pb-6' }}">
        @if (! $isHome)
            <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" wire:navigate class="font-medium text-zinc-700 transition hover:text-cyan-700 dark:text-zinc-300 dark:hover:text-cyan-200">Home</a>
                <span aria-hidden="true">/</span>
                <span class="font-medium text-zinc-950 dark:text-white" aria-current="page">Listings</span>
            </nav>
        @endif

        @if ($isHome)
            <div class="grid min-w-0 gap-6 overflow-hidden rounded-3xl border border-white/70 bg-white/80 p-5 shadow-2xl shadow-orange-950/10 sm:rounded-[2rem] sm:p-8 lg:grid-cols-[1.15fr_0.85fr] lg:gap-8 lg:p-12 dark:border-white/10 dark:bg-white/6">
                <div class="min-w-0 space-y-6">
                    <p class="inline-flex rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-orange-700 sm:tracking-[0.22em] dark:border-orange-300/15 dark:bg-orange-300/10 dark:text-orange-200">Verified rental listings</p>
                    <div class="space-y-4">
                        <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-zinc-950 sm:text-5xl dark:text-white">Find ready-to-rent homes without digging through unfinished inventory.</h1>
                        <p class="max-w-2xl text-base leading-7 text-zinc-600 sm:text-lg dark:text-zinc-300">Browse available homes with clear pricing, unit details, amenities, and viewing requests in one place.</p>
                    </div>
                    <div class="flex flex-col gap-3 min-[420px]:flex-row min-[420px]:flex-wrap">
                        <a href="{{ route('marketplace.index') }}" wire:navigate class="inline-flex justify-center rounded-full bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100">Browse Listings</a>
                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex justify-center rounded-full border border-zinc-300 bg-white px-5 py-3 text-sm font-semibold text-zinc-900 transition hover:border-zinc-950 dark:border-white/15 dark:bg-white/5 dark:text-white dark:hover:border-white/40">Open Dashboard</a>
                        @endauth
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
                    <article class="rounded-[1.5rem] bg-zinc-950 p-5 text-white dark:bg-white dark:text-zinc-950">
                        <p class="text-sm text-white/70 dark:text-zinc-500">Published listings</p>
                        <p class="mt-4 text-3xl font-semibold">{{ $properties->total() }}</p>
                        <p class="mt-2 text-sm text-white/70 dark:text-zinc-600">Homes currently open for viewing or application.</p>
                    </article>
                    <article class="rounded-[1.5rem] border border-emerald-200 bg-emerald-50 p-5 text-emerald-950 dark:border-emerald-300/15 dark:bg-emerald-300/10 dark:text-emerald-100">
                        <p class="text-sm text-emerald-700 dark:text-emerald-200">Cities covered</p>
                        <p class="mt-4 text-3xl font-semibold">{{ $cities->count() }}</p>
                        <p class="mt-2 text-sm text-emerald-700/80 dark:text-emerald-200/80">Find homes in the locations that matter to you.</p>
                    </article>
                    <article class="rounded-[1.5rem] border border-cyan-200 bg-cyan-50 p-5 text-cyan-950 dark:border-cyan-300/15 dark:bg-cyan-300/10 dark:text-cyan-100">
                        <p class="text-sm text-cyan-700 dark:text-cyan-200">Searchable inventory</p>
                        <p class="mt-4 text-3xl font-semibold">{{ $properties->count() }}</p>
                        <p class="mt-2 text-sm text-cyan-700/80 dark:text-cyan-200/80">Search by location, bedrooms, property type, or budget.</p>
                    </article>
                </div>
            </div>
        @else
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">Marketplace</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">Published rental listings</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-300">Search by area, property type, bedroom count, or budget to find active public inventory.</p>
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $properties->total() }} listing{{ $properties->total() === 1 ? '' : 's' }} found</p>
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid min-w-0 gap-6 lg:grid-cols-[300px_minmax(0,1fr)]">
            <aside class="h-fit rounded-[1.75rem] border border-white/70 bg-white/80 p-5 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Filter Listings</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Tighten the result set in real time.</p>
                    </div>
                    <button type="button" wire:click="clearFilters" class="text-sm font-medium text-cyan-700 transition hover:text-cyan-900 dark:text-cyan-300 dark:hover:text-cyan-200">Reset</button>
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="search" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Search</label>
                        <input id="search" type="search" wire:model.live.debounce.300ms="search" placeholder="Area, property, unit type" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white" />
                    </div>

                    <div>
                        <label for="city" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">City</label>
                        <select id="city" wire:model.live="city" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
                            <option value="">All cities</option>
                            @foreach ($cities as $cityOption)
                                <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="type" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Property type</label>
                        <select id="type" wire:model.live="type" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
                            <option value="">All property types</option>
                            @foreach ($propertyTypes as $propertyType)
                                <option value="{{ $propertyType->value }}">{{ $propertyType->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        <div>
                            <label for="bedrooms" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Bedrooms</label>
                            <select id="bedrooms" wire:model.live="bedrooms" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
                                <option value="">Any</option>
                                @foreach ($bedroomOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}+</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="max-rent" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Max rent</label>
                            <select id="max-rent" wire:model.live="maxRent" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
                                <option value="">No limit</option>
                                <option value="1500000">Up to NGN 1.5M</option>
                                <option value="2500000">Up to NGN 2.5M</option>
                                <option value="4000000">Up to NGN 4M</option>
                                <option value="6000000">Up to NGN 6M</option>
                            </select>
                        </div>
                    </div>

                    <label class="flex items-start gap-3 rounded-2xl border border-zinc-200/80 bg-zinc-50 px-4 py-3 text-sm text-zinc-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-200">
                        <input type="checkbox" wire:model.live="featuredOnly" class="mt-1 size-4 rounded border-zinc-300 text-cyan-600 focus:ring-cyan-500" />
                        <span>
                            <span class="block font-medium">Featured only</span>
                            <span class="mt-1 block text-zinc-500 dark:text-zinc-400">Show highlight-ready listings first.</span>
                        </span>
                    </label>
                </div>
            </aside>

            <div class="min-w-0 space-y-5">
                @forelse ($properties as $property)
                    @php
                        $primaryUnit = $property->publicUnits->first();
                        $coverUrl = $property->getFirstMediaUrl('gallery');
                    @endphp
                    <article class="overflow-hidden rounded-3xl border border-white/70 bg-white/85 shadow-xl shadow-cyan-950/5 transition hover:-translate-y-0.5 sm:rounded-[1.9rem] dark:border-white/10 dark:bg-white/6">
                        <div class="grid min-w-0 gap-0 lg:grid-cols-[320px_minmax(0,1fr)]">
                            <div class="relative min-h-64 overflow-hidden bg-zinc-200 dark:bg-zinc-900">
                                @if ($coverUrl !== '')
                                    <img src="{{ $coverUrl }}" alt="{{ $property->title }}" class="h-full w-full object-cover" />
                                @else
                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,#7dd3fc,transparent_35%),linear-gradient(135deg,#082f49,#164e63,#022c22)]"></div>
                                    <div class="absolute inset-0 flex items-end p-6 text-white/85">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.24em] text-white/60">LeaseSmart Listing</p>
                                            <p class="mt-2 text-2xl font-semibold">{{ $property->title }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if ($property->is_featured)
                                    <span class="absolute left-5 top-5 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-900">Featured</span>
                                @endif
                            </div>

                            <div class="p-6 lg:p-7">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium uppercase tracking-[0.16em] text-zinc-500 sm:text-sm sm:tracking-[0.2em] dark:text-zinc-400">{{ $property->property_type->label() }}</p>
                                        <h2 class="mt-2 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                                            <a href="{{ route('marketplace.show', $property) }}" wire:navigate class="break-words transition hover:text-cyan-700 dark:hover:text-cyan-300">{{ $property->title }}</a>
                                        </h2>
                                        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $property->address_line_1 }}, {{ $property->city }}, {{ $property->state }}</p>
                                    </div>
                                    @if ($primaryUnit)
                                        <div class="min-w-0 rounded-2xl bg-zinc-950 px-4 py-3 text-white dark:bg-white dark:text-zinc-950">
                                            <p class="text-xs uppercase tracking-[0.16em] text-white/65 sm:tracking-[0.2em] dark:text-zinc-500">Starting from</p>
                                            <p class="mt-1 break-words text-xl font-semibold">NGN {{ number_format((float) $primaryUnit->rent_amount) }}</p>
                                            <p class="text-xs text-white/65 dark:text-zinc-500">per {{ strtolower($primaryUnit->billing_cycle->label()) }}</p>
                                        </div>
                                    @endif
                                </div>

                                <p class="mt-5 line-clamp-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $property->description }}</p>

                                <div class="mt-5 flex flex-wrap gap-2">
                                    @foreach ($property->amenities->take(4) as $amenity)
                                        <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-medium text-zinc-700 dark:border-white/10 dark:bg-white/8 dark:text-zinc-200">{{ $amenity->name }}</span>
                                    @endforeach
                                    @if ($property->amenities->count() > 4)
                                        <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-medium text-zinc-500 dark:border-white/10 dark:bg-white/8 dark:text-zinc-400">+{{ $property->amenities->count() - 4 }} more</span>
                                    @endif
                                </div>

                                <div class="mt-6 grid gap-3 text-sm text-zinc-600 sm:grid-cols-3 dark:text-zinc-300">
                                    <div class="rounded-2xl border border-zinc-200/80 bg-zinc-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Visible units</p>
                                        <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $property->publicUnits->count() }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-zinc-200/80 bg-zinc-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Bedrooms</p>
                                        <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $primaryUnit?->bedrooms ?? 'N/A' }}{{ $primaryUnit ? '+' : '' }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-zinc-200/80 bg-zinc-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Status</p>
                                        <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $primaryUnit?->occupancy_status->label() ?? 'Available' }}</p>
                                    </div>
                                </div>

                                <div class="mt-6 flex flex-col gap-4 min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Published {{ optional($property->published_at)->diffForHumans() }}</p>
                                    <a href="{{ route('marketplace.show', $property) }}" wire:navigate class="inline-flex justify-center rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">View Property</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.9rem] border border-dashed border-zinc-300 bg-white/70 p-10 text-center dark:border-white/10 dark:bg-white/5">
                        <h3 class="text-xl font-semibold text-zinc-950 dark:text-white">No listings match this filter set yet.</h3>
                        <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Try widening your budget, removing the bedroom filter, or resetting to all cities.</p>
                        <button type="button" wire:click="clearFilters" class="mt-5 rounded-full bg-zinc-950 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-zinc-950">Reset Filters</button>
                    </div>
                @endforelse

                <div>{{ $properties->links() }}</div>
            </div>
        </div>
    </section>
</div>
