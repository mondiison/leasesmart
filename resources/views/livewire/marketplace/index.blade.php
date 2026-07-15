<div class="pb-16">
    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8 {{ $isHome ? 'pb-8' : 'pb-6' }}">
        @if (! $isHome)
            <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" wire:navigate class="font-medium text-zinc-700 transition hover:text-teal-700 dark:text-zinc-300 dark:hover:text-teal-200">Home</a>
                <span aria-hidden="true">/</span>
                <span class="font-medium text-zinc-950 dark:text-white" aria-current="page">Listings</span>
            </nav>
        @endif

        <div class="overflow-hidden rounded-[2rem] border border-zinc-200/80 bg-white p-5 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:p-7 lg:p-9 dark:border-white/10 dark:bg-white/6">
            <div class="flex flex-col gap-8 lg:min-h-[22rem] lg:justify-between">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-teal-700 dark:text-teal-300">Verified rental listings</p>
                        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-zinc-950 sm:text-6xl dark:text-white">
                            Find your next home without the listing noise.
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-7 text-zinc-600 sm:text-lg dark:text-zinc-300">
                            Browse available homes with clear pricing, unit details, amenities, and inspection requests in one calm search flow.
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-3 text-center lg:w-80">
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $properties->total() }}</p>
                            <p class="mt-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">Listings</p>
                        </div>
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $cities->count() }}</p>
                            <p class="mt-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">Cities</p>
                        </div>
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $properties->count() }}</p>
                            <p class="mt-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">Shown</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.5rem] border border-zinc-200/80 bg-white p-2 shadow-[0_18px_55px_rgba(15,23,42,0.07)] dark:border-white/10 dark:bg-zinc-950/70">
                    <div class="grid gap-2 lg:grid-cols-[minmax(220px,1.5fr)_minmax(160px,0.85fr)_minmax(160px,0.85fr)_minmax(160px,0.85fr)_150px] lg:items-center">
                        <label for="search" class="flex min-w-0 items-center gap-3 rounded-[1.15rem] px-4 py-3 transition focus-within:bg-zinc-50 dark:focus-within:bg-white/5">
                            <svg class="size-5 shrink-0 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11s-8-6-8-11a8 8 0 1 1 16 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
                            </svg>
                            <span class="min-w-0 flex-1">
                                <span class="block text-xs font-semibold text-zinc-800 dark:text-zinc-100">Where</span>
                                <input id="search" type="search" wire:model.live.debounce.300ms="search" placeholder="Area, city, property, unit" class="mt-1 w-full border-0 bg-transparent p-0 text-sm text-zinc-950 outline-none placeholder:text-zinc-400 focus:ring-0 dark:text-white" />
                            </span>
                        </label>

                        <label for="city" class="flex min-w-0 items-center gap-3 rounded-[1.15rem] border-t border-zinc-100 px-4 py-3 lg:border-l lg:border-t-0 dark:border-white/10">
                            <svg class="size-5 shrink-0 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 21V6l8-3 8 3v15" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 21v-8h6v8M9 8h.01M15 8h.01" />
                            </svg>
                            <span class="min-w-0 flex-1">
                                <span class="block text-xs font-semibold text-zinc-800 dark:text-zinc-100">City</span>
                                <select id="city" wire:model.live="city" class="mt-1 w-full border-0 bg-transparent p-0 text-sm font-medium text-zinc-950 outline-none focus:ring-0 dark:text-white">
                                    <option value="">All cities</option>
                                    @foreach ($cities as $cityOption)
                                        <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                                    @endforeach
                                </select>
                            </span>
                        </label>

                        <label for="max-rent" class="flex min-w-0 items-center gap-3 rounded-[1.15rem] border-t border-zinc-100 px-4 py-3 lg:border-l lg:border-t-0 dark:border-white/10">
                            <svg class="size-5 shrink-0 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M6 7v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6" />
                            </svg>
                            <span class="min-w-0 flex-1">
                                <span class="block text-xs font-semibold text-zinc-800 dark:text-zinc-100">Budget</span>
                                <select id="max-rent" wire:model.live="maxRent" class="mt-1 w-full border-0 bg-transparent p-0 text-sm font-medium text-zinc-950 outline-none focus:ring-0 dark:text-white">
                                    <option value="">Any budget</option>
                                    <option value="1500000">Up to NGN 1.5M</option>
                                    <option value="2500000">Up to NGN 2.5M</option>
                                    <option value="4000000">Up to NGN 4M</option>
                                    <option value="6000000">Up to NGN 6M</option>
                                </select>
                            </span>
                        </label>

                        <label for="bedrooms" class="flex min-w-0 items-center gap-3 rounded-[1.15rem] border-t border-zinc-100 px-4 py-3 lg:border-l lg:border-t-0 dark:border-white/10">
                            <svg class="size-5 shrink-0 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 11h18v8M5 11V7a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v4M7 11V9h4v2M13 11V9h4v2" />
                            </svg>
                            <span class="min-w-0 flex-1">
                                <span class="block text-xs font-semibold text-zinc-800 dark:text-zinc-100">Bedrooms</span>
                                <select id="bedrooms" wire:model.live="bedrooms" class="mt-1 w-full border-0 bg-transparent p-0 text-sm font-medium text-zinc-950 outline-none focus:ring-0 dark:text-white">
                                    <option value="">Any beds</option>
                                    @foreach ($bedroomOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}+ beds</option>
                                    @endforeach
                                </select>
                            </span>
                        </label>

                        <button type="button" class="inline-flex h-14 items-center justify-center rounded-[1.15rem] bg-teal-600 px-6 text-base font-semibold text-white shadow-lg shadow-teal-900/15 transition hover:bg-teal-700">
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid min-w-0 gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="h-fit rounded-[1.5rem] border border-zinc-200/80 bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.06)] dark:border-white/10 dark:bg-white/6">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="grid size-9 place-items-center rounded-full bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10M10 18h4" />
                            </svg>
                        </span>
                        <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Filters</h2>
                    </div>
                    <button type="button" wire:click="clearFilters" class="text-xs font-semibold text-teal-700 transition hover:text-teal-900 dark:text-teal-300 dark:hover:text-teal-200">Clear all</button>
                </div>

                <div class="mt-6 space-y-6">
                    <div>
                        <label for="type" class="mb-2 block text-xs font-semibold text-zinc-700 dark:text-zinc-200">Property Type</label>
                        <select id="type" wire:model.live="type" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-950 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/15 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
                            <option value="">All property types</option>
                            @foreach ($propertyTypes as $propertyType)
                                <option value="{{ $propertyType->value }}">{{ $propertyType->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-200">Price Range</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $maxRent === '' ? 'No limit' : 'NGN '.number_format((float) $maxRent) }}</p>
                        </div>
                        <div class="mt-3 grid gap-2">
                            @foreach ([1500000 => '1.5M', 2500000 => '2.5M', 4000000 => '4M', 6000000 => '6M'] as $rentValue => $rentLabel)
                                <button type="button" wire:click="$set('maxRent', '{{ $rentValue }}')" class="flex items-center justify-between rounded-xl border px-3 py-2 text-left text-sm transition {{ $maxRent === (string) $rentValue ? 'border-teal-600 bg-teal-50 text-teal-800 dark:border-teal-300/40 dark:bg-teal-300/10 dark:text-teal-100' : 'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300' }}">
                                    <span>Up to NGN {{ $rentLabel }}</span>
                                    <span class="size-2 rounded-full {{ $maxRent === (string) $rentValue ? 'bg-teal-600 dark:bg-teal-300' : 'bg-zinc-300 dark:bg-zinc-600' }}"></span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-200">Bedrooms</p>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            <button type="button" wire:click="$set('bedrooms', '')" class="rounded-xl border px-3 py-2 text-sm font-semibold transition {{ $bedrooms === '' ? 'border-teal-600 bg-teal-600 text-white' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300' }}">Any</button>
                            @foreach ([2, 3, 4, 5] as $option)
                                <button type="button" wire:click="$set('bedrooms', '{{ $option }}')" class="rounded-xl border px-3 py-2 text-sm font-semibold transition {{ $bedrooms === (string) $option ? 'border-teal-600 bg-teal-600 text-white' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300' }}">{{ $option }}+</button>
                            @endforeach
                        </div>
                    </div>

                    <label class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-3 text-sm text-zinc-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-200">
                        <input type="checkbox" wire:model.live="featuredOnly" class="mt-0.5 size-4 rounded border-zinc-300 text-teal-600 focus:ring-teal-500" />
                        <span>
                            <span class="block font-semibold">Featured only</span>
                            <span class="mt-1 block text-xs leading-5 text-zinc-500 dark:text-zinc-400">Prioritize highlight-ready listings.</span>
                        </span>
                    </label>
                </div>
            </aside>

            <div class="min-w-0">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white">{{ $properties->total() }} result{{ $properties->total() === 1 ? '' : 's' }}</p>
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                        <span>Sort by: <span class="text-zinc-950 dark:text-white">Newest</span></span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-teal-700 ring-1 ring-zinc-200 dark:bg-white/6 dark:text-teal-300 dark:ring-white/10">
                            <span class="size-2 rounded-full bg-teal-500"></span>
                            Search as I move
                        </span>
                    </div>
                </div>

                <div class="space-y-5">
                    @forelse ($properties as $property)
                        @php
                            $primaryUnit = $property->publicUnits->first();
                            $coverUrl = $property->getFirstMediaUrl('gallery');
                        @endphp
                        <article class="overflow-hidden rounded-[1.5rem] border border-zinc-200/80 bg-white shadow-[0_18px_45px_rgba(15,23,42,0.055)] transition hover:-translate-y-0.5 hover:shadow-[0_24px_60px_rgba(15,23,42,0.08)] dark:border-white/10 dark:bg-white/6">
                            <div class="grid min-w-0 gap-0 md:grid-cols-[260px_minmax(0,1fr)]">
                                <a href="{{ route('marketplace.show', $property) }}" wire:navigate class="relative block aspect-[4/3] overflow-hidden bg-zinc-200 md:aspect-auto md:min-h-52 dark:bg-zinc-900">
                                    @if ($coverUrl !== '')
                                        <img src="{{ $coverUrl }}" alt="{{ $property->title }}" class="h-full w-full object-cover transition duration-500 hover:scale-105" />
                                    @else
                                        <div class="absolute inset-0 bg-[linear-gradient(135deg,#f0fdfa,#dbeafe_48%,#f8fafc)]"></div>
                                        <div class="absolute inset-0 flex items-end p-5 text-zinc-900">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.2em] text-zinc-500">LeaseSmart Listing</p>
                                                <p class="mt-2 text-xl font-semibold">{{ $property->title }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($property->is_featured)
                                        <span class="absolute left-4 top-4 rounded-full bg-white/92 px-3 py-1 text-xs font-semibold text-teal-700 shadow-sm">Featured</span>
                                    @endif
                                </a>

                                <div class="min-w-0 p-5 sm:p-6">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-700 dark:text-teal-300">{{ $property->property_type->label() }}</p>
                                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                                                <a href="{{ route('marketplace.show', $property) }}" wire:navigate class="break-words transition hover:text-teal-700 dark:hover:text-teal-300">{{ $property->title }}</a>
                                            </h2>
                                            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $property->address_line_1 }}, {{ $property->city }}, {{ $property->state }}</p>
                                        </div>

                                        @if ($primaryUnit)
                                            <div class="shrink-0 text-left lg:text-right">
                                                <p class="text-xl font-semibold text-teal-700 dark:text-teal-300">NGN {{ number_format((float) $primaryUnit->rent_amount) }}</p>
                                                <p class="mt-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">per {{ strtolower($primaryUnit->billing_cycle->label()) }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <p class="mt-4 line-clamp-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $property->description }}</p>

                                    <div class="mt-5 flex flex-wrap gap-x-6 gap-y-3 text-sm text-zinc-700 dark:text-zinc-300">
                                        <span>{{ $primaryUnit?->bedrooms ?? 'N/A' }}{{ $primaryUnit ? '+' : '' }} Beds</span>
                                        <span>{{ $property->publicUnits->count() }} visible unit{{ $property->publicUnits->count() === 1 ? '' : 's' }}</span>
                                        <span>{{ $primaryUnit?->occupancy_status->label() ?? 'Available' }}</span>
                                    </div>

                                    <div class="mt-5 flex flex-col gap-4 min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($property->amenities->take(3) as $amenity)
                                                <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-medium text-zinc-600 dark:border-white/10 dark:bg-white/8 dark:text-zinc-300">{{ $amenity->name }}</span>
                                            @endforeach
                                            @if ($property->amenities->count() > 3)
                                                <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-medium text-zinc-500 dark:border-white/10 dark:bg-white/8 dark:text-zinc-400">+{{ $property->amenities->count() - 3 }} more</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('marketplace.show', $property) }}" wire:navigate class="inline-flex shrink-0 justify-center rounded-full bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100">View Property</a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-zinc-300 bg-white p-10 text-center shadow-[0_18px_45px_rgba(15,23,42,0.04)] dark:border-white/10 dark:bg-white/5">
                            <h3 class="text-xl font-semibold text-zinc-950 dark:text-white">No listings match this filter set yet.</h3>
                            <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Try widening your budget, removing the bedroom filter, or resetting to all cities.</p>
                            <button type="button" wire:click="clearFilters" class="mt-5 rounded-full bg-zinc-950 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-zinc-950">Reset Filters</button>
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">{{ $properties->links() }}</div>
            </div>
        </div>
    </section>
</div>
