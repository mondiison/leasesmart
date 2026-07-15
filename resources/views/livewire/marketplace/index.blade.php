<div class="pb-16">
    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
        @if (! $isHome)
            <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" wire:navigate class="font-medium text-zinc-700 transition hover:text-sky-700 dark:text-zinc-300 dark:hover:text-sky-200">Home</a>
                <span aria-hidden="true">/</span>
                <span class="font-medium text-zinc-950 dark:text-white" aria-current="page">Listings</span>
            </nav>
        @endif

        <div class="rounded-[2rem] bg-[#fbfcfc] p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:p-8 lg:p-10 dark:bg-zinc-900">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-sky-700 dark:text-sky-300">Verified homes, clear terms, faster viewings</p>
                    <h1 class="mt-5 max-w-4xl text-4xl font-semibold leading-[1.02] tracking-tight text-zinc-950 sm:text-6xl dark:text-white">
                        Find your next home with a calmer, smarter search.
                    </h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-zinc-600 sm:text-lg dark:text-zinc-300">
                        Compare polished rental listings by location, budget, bedrooms, amenities, and viewing readiness without digging through unfinished inventory.
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-5 text-center shadow-sm dark:border-white/10 dark:bg-white/5">
                        <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $properties->total() }}</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Listings</p>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-5 text-center shadow-sm dark:border-white/10 dark:bg-white/5">
                        <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $cities->count() }}</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Cities</p>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-5 text-center shadow-sm dark:border-white/10 dark:bg-white/5">
                        <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $properties->count() }}</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Shown</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 rounded-[1.35rem] bg-white p-2 shadow-[0_22px_60px_rgba(15,23,42,0.10)] dark:bg-zinc-950/70 dark:shadow-[0_22px_60px_rgba(0,0,0,0.28)]">
            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-[minmax(260px,1.5fr)_minmax(150px,0.8fr)_minmax(170px,0.9fr)_minmax(150px,0.75fr)_128px] lg:items-center">
                    <label for="search" class="flex min-w-0 items-center gap-2 rounded-2xl px-3 py-2.5 transition focus-within:bg-zinc-50 dark:focus-within:bg-white/5">
                        <svg class="size-5 shrink-0 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11s-8-6-8-11a8 8 0 1 1 16 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
                        </svg>
                        <span class="min-w-0 flex-1">
                            <span class="block text-xs font-semibold text-zinc-800 dark:text-zinc-100">Where</span>
                            <input id="search" type="search" wire:model.live.debounce.300ms="search" placeholder="Neighborhood, city, property" class="mt-1 w-full border-0 bg-transparent p-0 text-sm text-zinc-950 outline-none placeholder:text-zinc-400 focus:ring-0 dark:text-white" />
                        </span>
                    </label>

                    <label for="city" class="flex min-w-0 items-center gap-2 rounded-2xl border-t border-zinc-100 px-3 py-2.5 md:border-l md:border-t-0 dark:border-white/10">
                        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-white/10 dark:text-zinc-300">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 21V7l5-3 5 3v14" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h6v11M8 9h.01M8 13h.01M8 17h.01M17 14h.01M17 18h.01" />
                            </svg>
                        </span>
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

                    <label for="hero-budget" class="flex min-w-0 items-center gap-2 rounded-2xl border-t border-zinc-100 px-3 py-2.5 lg:border-l lg:border-t-0 dark:border-white/10">
                        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-white/10 dark:text-zinc-300">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M6 12h12M8 19V5l8 14V5" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-xs font-semibold text-zinc-800 dark:text-zinc-100">Budget</span>
                            <select id="hero-budget" wire:model.live="maxRent" class="mt-1 w-full border-0 bg-transparent p-0 text-sm font-medium text-zinc-950 outline-none focus:ring-0 dark:text-white">
                                <option value="">Any budget</option>
                                <option value="1500000">Up to NGN 1.5M</option>
                                <option value="2500000">Up to NGN 2.5M</option>
                                <option value="4000000">Up to NGN 4M</option>
                                <option value="6000000">Up to NGN 6M</option>
                                <option value="8000000">Up to NGN 8M</option>
                            </select>
                        </span>
                    </label>

                    <label for="hero-beds" class="flex min-w-0 items-center gap-2 rounded-2xl border-t border-zinc-100 px-3 py-2.5 lg:border-l lg:border-t-0 dark:border-white/10">
                        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-white/10 dark:text-zinc-300">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 11h18v8M5 11V7a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v4M7 11V9h4v2M13 11V9h4v2" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-xs font-semibold text-zinc-800 dark:text-zinc-100">Beds</span>
                            <select id="hero-beds" wire:model.live="bedrooms" class="mt-1 w-full border-0 bg-transparent p-0 text-sm font-medium text-zinc-950 outline-none focus:ring-0 dark:text-white">
                                <option value="">Any beds</option>
                                @foreach ($bedroomOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}+ beds</option>
                                @endforeach
                            </select>
                        </span>
                    </label>

                    <button type="button" class="inline-flex h-12 items-center justify-center rounded-2xl bg-sky-600 px-5 text-sm font-semibold text-white shadow-lg shadow-sky-900/15 transition hover:bg-sky-700">
                        Search
                    </button>
            </div>
        </div>
    </section>

    <section class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 flex-col gap-5 rounded-[1.8rem] bg-white p-4 shadow-[0_24px_70px_rgba(15,23,42,0.08)] lg:flex-row lg:p-5 dark:bg-zinc-900/70">
            <aside class="h-fit min-w-0 rounded-[1.35rem] bg-zinc-50 p-4 shadow-[0_18px_48px_rgba(15,23,42,0.09)] lg:basis-4/12 dark:bg-white/6 dark:shadow-[0_18px_48px_rgba(0,0,0,0.24)]">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-950 dark:text-white">Filters</h2>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Fine tune the homes shown.</p>
                    </div>
                    <button type="button" wire:click="clearFilters" class="text-xs font-semibold text-sky-700 transition hover:text-sky-900 dark:text-sky-300 dark:hover:text-sky-200">Clear all</button>
                </div>

                <div class="mt-5 space-y-5">
                    <div>
                        <div class="flex items-baseline justify-between gap-3">
                            <label for="price-range" class="text-xs font-semibold text-zinc-800 dark:text-zinc-100">Price Range</label>
                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $maxRent === '' ? 'Any budget' : 'Up to NGN '.number_format((float) $maxRent) }}</span>
                        </div>
                        <input id="price-range" type="range" min="1000000" max="8000000" step="250000" wire:model.live="maxRent" class="mt-3 h-1.5 w-full accent-sky-600" />
                        <div class="mt-2 flex justify-between text-[11px] font-medium text-zinc-400">
                            <span>NGN 1M</span>
                            <span>NGN 8M+</span>
                        </div>
                        <button type="button" wire:click="$set('maxRent', '')" class="mt-3 text-xs font-semibold text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">Any price</button>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-zinc-800 dark:text-zinc-100">Bedrooms</p>
                        <div class="mt-2 flex gap-1.5">
                            <button type="button" wire:click="$set('bedrooms', '')" class="h-9 flex-1 rounded-lg border px-2 text-xs font-semibold transition {{ $bedrooms === '' ? 'border-sky-600 bg-sky-600 text-white shadow-sm' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300' }}">Any</button>
                            @foreach ([2, 3, 4] as $option)
                                <button type="button" wire:click="$set('bedrooms', '{{ $option }}')" class="h-9 flex-1 rounded-lg border px-2 text-xs font-semibold transition {{ $bedrooms === (string) $option ? 'border-sky-600 bg-sky-600 text-white shadow-sm' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300' }}">{{ $option }}+</button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-zinc-800 dark:text-zinc-100">Bathrooms</p>
                        <div class="mt-2 flex gap-1.5">
                            <button type="button" wire:click="$set('bathrooms', '')" class="h-9 flex-1 rounded-lg border px-2 text-xs font-semibold transition {{ $bathrooms === '' ? 'border-sky-600 bg-sky-600 text-white shadow-sm' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300' }}">Any</button>
                            @foreach ([1, 2, 3] as $option)
                                <button type="button" wire:click="$set('bathrooms', '{{ $option }}')" class="h-9 flex-1 rounded-lg border px-2 text-xs font-semibold transition {{ $bathrooms === (string) $option ? 'border-sky-600 bg-sky-600 text-white shadow-sm' : 'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300' }}">{{ $option }}+</button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-zinc-800 dark:text-zinc-100">Property Types</p>
                        <div class="mt-2 grid grid-cols-1 gap-1.5 min-[520px]:grid-cols-2 lg:grid-cols-3">
                            @foreach ($propertyTypes as $propertyType)
                                <label class="flex min-w-0 items-center gap-1.5 rounded-lg bg-white px-2 py-2 text-[11px] font-medium text-zinc-700 transition hover:bg-sky-50 dark:bg-white/5 dark:text-zinc-200">
                                    <input type="checkbox" wire:model.live="types" value="{{ $propertyType->value }}" class="size-3.5 shrink-0 rounded border-zinc-300 text-sky-600 focus:ring-sky-500" />
                                    <span class="truncate">{{ $propertyType->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-zinc-800 dark:text-zinc-100">Amenities</p>
                        <div class="mt-2 grid grid-cols-1 gap-1.5 min-[520px]:grid-cols-2 lg:grid-cols-3">
                            @forelse ($amenities as $amenity)
                                <label class="flex min-w-0 items-center gap-1.5 rounded-lg bg-white px-2 py-2 text-[11px] font-medium text-zinc-700 transition hover:bg-sky-50 dark:bg-white/5 dark:text-zinc-200">
                                    <input type="checkbox" wire:model.live="amenityIds" value="{{ $amenity->id }}" class="size-3.5 shrink-0 rounded border-zinc-300 text-sky-600 focus:ring-sky-500" />
                                    <span class="truncate">{{ $amenity->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">No amenities available yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <label class="flex items-start gap-3 rounded-xl border border-sky-200 bg-sky-50 px-3 py-3 text-sm text-sky-950 dark:border-sky-300/20 dark:bg-sky-300/10 dark:text-sky-100">
                        <input type="checkbox" wire:model.live="featuredOnly" class="mt-0.5 size-4 rounded border-sky-300 text-sky-600 focus:ring-sky-500" />
                        <span>
                            <span class="block font-semibold">Featured only</span>
                            <span class="mt-1 block text-xs leading-5 text-sky-700/80 dark:text-sky-100/70">Show editor-ready listings first.</span>
                        </span>
                    </label>
                </div>
            </aside>

            <div class="min-w-0 lg:basis-8/12">
                <div class="mb-4 flex flex-col gap-3 rounded-[1.25rem] border border-zinc-200/80 bg-white px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-white/10 dark:bg-white/6">
                    <p class="text-sm font-semibold text-zinc-950 dark:text-white">{{ $properties->total() }} result{{ $properties->total() === 1 ? '' : 's' }}</p>
                    <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                        <span>Sort by: <span class="text-zinc-950 dark:text-white">Newest</span></span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1.5 text-sky-700 ring-1 ring-sky-100 dark:bg-sky-300/10 dark:text-sky-300 dark:ring-sky-300/20">
                            <span class="size-2 rounded-full bg-sky-500"></span>
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
                        <article class="overflow-hidden rounded-[1.5rem] border border-zinc-200/80 bg-white shadow-[0_22px_60px_rgba(15,23,42,0.07)] transition hover:-translate-y-0.5 hover:shadow-[0_28px_70px_rgba(15,23,42,0.10)] dark:border-white/10 dark:bg-white/6">
                            <div class="grid min-w-0 gap-0 md:grid-cols-[280px_minmax(0,1fr)]">
                                <a href="{{ route('marketplace.show', $property) }}" wire:navigate class="relative block aspect-[4/3] overflow-hidden bg-zinc-200 md:aspect-auto md:min-h-56 dark:bg-zinc-900">
                                    @if ($coverUrl !== '')
                                        <img src="{{ $coverUrl }}" alt="{{ $property->title }}" class="h-full w-full object-cover transition duration-500 hover:scale-105" />
                                    @else
                                        <div class="absolute inset-0 bg-[linear-gradient(135deg,#ecfeff,#f8fafc_48%,#dbeafe)]"></div>
                                        <div class="absolute inset-0 flex items-end p-5 text-zinc-900">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.2em] text-zinc-500">LeaseSmart Listing</p>
                                                <p class="mt-2 text-xl font-semibold">{{ $property->title }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($property->is_featured)
                                        <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-sky-700 shadow-sm">Featured</span>
                                    @endif
                                </a>

                                <div class="min-w-0 p-5 sm:p-6">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700 dark:text-sky-300">{{ $property->property_type->label() }}</p>
                                            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">
                                                <a href="{{ route('marketplace.show', $property) }}" wire:navigate class="break-words transition hover:text-sky-700 dark:hover:text-sky-300">{{ $property->title }}</a>
                                            </h2>
                                            <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $property->address_line_1 }}, {{ $property->city }}, {{ $property->state }}</p>
                                        </div>

                                        @if ($primaryUnit)
                                            <div class="shrink-0 rounded-2xl bg-sky-50 px-4 py-3 text-sky-950 lg:text-right dark:bg-sky-300/10 dark:text-sky-100">
                                                <p class="text-xl font-semibold">NGN {{ number_format((float) $primaryUnit->rent_amount) }}</p>
                                                <p class="mt-1 text-xs font-medium text-sky-700/70 dark:text-sky-100/65">per {{ strtolower($primaryUnit->billing_cycle->label()) }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <p class="mt-4 line-clamp-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $property->description }}</p>

                                    <div class="mt-5 flex flex-wrap gap-x-6 gap-y-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        <span>{{ $primaryUnit?->bedrooms ?? 'N/A' }}{{ $primaryUnit ? '+' : '' }} Beds</span>
                                        <span>{{ $primaryUnit?->bathrooms ?? 'N/A' }}{{ $primaryUnit ? '+' : '' }} Baths</span>
                                        <span>{{ $property->publicUnits->count() }} visible unit{{ $property->publicUnits->count() === 1 ? '' : 's' }}</span>
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
                                        <a href="{{ route('marketplace.show', $property) }}" wire:navigate class="inline-flex shrink-0 justify-center rounded-full bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">View Property</a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-zinc-300 bg-white p-10 text-center shadow-[0_18px_45px_rgba(15,23,42,0.04)] dark:border-white/10 dark:bg-white/5">
                            <h3 class="text-xl font-semibold text-zinc-950 dark:text-white">No listings match this filter set yet.</h3>
                            <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Try widening your budget, removing the bedroom or bathroom filters, or clearing amenities.</p>
                            <button type="button" wire:click="clearFilters" class="mt-5 rounded-full bg-zinc-950 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-zinc-950">Reset Filters</button>
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">{{ $properties->links() }}</div>
            </div>
        </div>
    </section>
</div>

