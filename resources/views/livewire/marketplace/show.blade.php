<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8 lg:py-12">
    <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" wire:navigate class="font-medium text-zinc-700 transition hover:text-cyan-700 dark:text-zinc-300 dark:hover:text-cyan-200">Home</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('marketplace.index') }}" wire:navigate class="font-medium text-zinc-700 transition hover:text-cyan-700 dark:text-zinc-300 dark:hover:text-cyan-200">Listings</a>
        <span aria-hidden="true">/</span>
        <span class="min-w-0 break-words font-medium text-zinc-950 dark:text-white" aria-current="page">{{ $property->title }}</span>
    </nav>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-[0.16em] text-zinc-500 sm:text-sm sm:tracking-[0.22em] dark:text-zinc-400">Property Detail</p>
            <h1 class="mt-2 break-words text-3xl font-semibold tracking-tight text-zinc-950 sm:text-4xl dark:text-white">{{ $property->title }}</h1>
            <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $property->address_line_1 }}, {{ $property->city }}, {{ $property->state }}, {{ $property->country }}</p>
        </div>
        <div class="flex flex-col gap-3 min-[420px]:flex-row sm:flex-wrap">
            <a href="{{ route('marketplace.index') }}" wire:navigate class="inline-flex justify-center rounded-full border border-zinc-300/80 px-4 py-2 text-sm font-medium text-zinc-900 transition hover:border-zinc-950 dark:border-white/15 dark:text-white dark:hover:border-white/40">Back to Listings</a>
            <span class="inline-flex justify-center rounded-full bg-zinc-950 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-zinc-950">{{ $property->property_type->label() }}</span>
        </div>
    </div>

    <div class="mt-6 grid min-w-0 gap-6 lg:mt-8 lg:grid-cols-[minmax(0,1fr)_minmax(320px,380px)] lg:gap-8">
        <div class="min-w-0 space-y-6 lg:space-y-8">
            <section class="overflow-hidden rounded-3xl border border-white/70 bg-white/85 shadow-xl shadow-cyan-950/5 sm:rounded-[2rem] dark:border-white/10 dark:bg-white/6">
                @php
                    $propertyGallery = $property->getMedia('gallery')->sortBy('order_column')->values();
                    $activePropertyMedia = $propertyGallery->get($activePropertyImage);
                @endphp
                <div class="relative h-64 bg-zinc-200 sm:h-80 lg:h-96 dark:bg-zinc-900">
                    @if ($activePropertyMedia)
                        <img src="{{ $activePropertyMedia->getUrl() }}" alt="{{ $property->title }} image {{ $activePropertyImage + 1 }}" class="h-full w-full object-cover" />

                        @if ($propertyGallery->count() > 1)
                            <div class="absolute inset-x-4 top-1/2 flex -translate-y-1/2 items-center justify-between">
                                <button type="button" wire:click="previousPropertyImage" class="inline-flex size-10 items-center justify-center rounded-full bg-zinc-950/70 text-white shadow-lg ring-1 ring-white/20 transition hover:bg-zinc-950" aria-label="Previous property image">
                                    <span aria-hidden="true">&lsaquo;</span>
                                </button>
                                <button type="button" wire:click="nextPropertyImage" class="inline-flex size-10 items-center justify-center rounded-full bg-zinc-950/70 text-white shadow-lg ring-1 ring-white/20 transition hover:bg-zinc-950" aria-label="Next property image">
                                    <span aria-hidden="true">&rsaquo;</span>
                                </button>
                            </div>

                            <div class="absolute bottom-4 left-4 rounded-full bg-zinc-950/75 px-3 py-1 text-xs font-semibold text-white ring-1 ring-white/15">
                                {{ $activePropertyImage + 1 }} / {{ $propertyGallery->count() }}
                            </div>
                        @endif
                    @else
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,#38bdf8,transparent_30%),linear-gradient(135deg,#0f172a,#164e63,#082f49)]"></div>
                        <div class="absolute inset-0 flex items-end p-5 text-white sm:p-8">
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] text-white/65 sm:tracking-[0.24em]">LeaseSmart Property</p>
                                <p class="mt-2 break-words text-2xl font-semibold sm:text-3xl">{{ $property->title }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                @if ($propertyGallery->count() > 1)
                    <div class="border-b border-zinc-200/80 bg-white/80 p-3 dark:border-white/10 dark:bg-zinc-950/40">
                        <div class="flex gap-2 overflow-x-auto pb-1">
                            @foreach ($propertyGallery as $index => $media)
                                <button type="button" wire:click="showPropertyImage({{ $index }})" class="h-16 w-24 shrink-0 overflow-hidden rounded-2xl border transition {{ $activePropertyImage === $index ? 'border-cyan-500 ring-2 ring-cyan-500/30' : 'border-zinc-200 opacity-75 hover:opacity-100 dark:border-white/10' }}" aria-label="Show property image {{ $index + 1 }}">
                                    <img src="{{ $media->getUrl() }}" alt="{{ $property->title }} thumbnail {{ $index + 1 }}" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="p-4 sm:p-6 lg:p-8">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <article class="min-w-0 rounded-2xl border border-zinc-200/80 bg-zinc-50 p-4 sm:rounded-[1.5rem] dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">Published units</p>
                            <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ $property->publicUnits->count() }}</p>
                        </article>
                        <article class="min-w-0 rounded-2xl border border-zinc-200/80 bg-zinc-50 p-4 sm:rounded-[1.5rem] dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">Starting rent</p>
                            <p class="mt-2 break-words text-2xl font-semibold text-zinc-950 dark:text-white">NGN {{ number_format((float) $property->publicUnits->min('rent_amount')) }}</p>
                        </article>
                        <article class="min-w-0 rounded-2xl border border-zinc-200/80 bg-zinc-50 p-4 sm:rounded-[1.5rem] dark:border-white/10 dark:bg-white/5">
                            <p class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">Available from</p>
                            <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">
                                {{ $property->publicUnits->pluck('available_from')->filter()->sort()->first()?->format('M j, Y') ?? 'Now' }}
                            </p>
                        </article>
                    </div>

                    <div class="mt-6 grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_220px] lg:gap-8">
                        <div class="min-w-0">
                            <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Overview</h2>
                            <p class="mt-3 text-sm leading-7 text-zinc-600 dark:text-zinc-300">{{ $property->description }}</p>
                        </div>
                        <dl class="space-y-4 rounded-2xl border border-zinc-200/80 bg-zinc-50 p-5 text-sm sm:rounded-[1.5rem] dark:border-white/10 dark:bg-white/5">
                            <div>
                                <dt class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">City</dt>
                                <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $property->city }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">State</dt>
                                <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $property->state }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">Code</dt>
                                <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $property->property_code }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-white/70 bg-white/85 p-4 shadow-xl shadow-cyan-950/5 sm:rounded-[2rem] sm:p-6 dark:border-white/10 dark:bg-white/6 lg:p-8">
                <h2 class="text-xl font-semibold text-zinc-950 sm:text-2xl dark:text-white">Available Units</h2>
                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Only listed units that are currently vacant or reserved are shown on the public marketplace.</p>

                <div class="mt-6 grid gap-5">
                    @foreach ($property->publicUnits as $unit)
                        @php
                            $unitGallery = $unit->getMedia('gallery')->sortBy('order_column')->values();
                            $activeUnitImage = (int) ($activeUnitImages[$unit->id] ?? 0);
                            $activeUnitMedia = $unitGallery->get($activeUnitImage);
                        @endphp
                        <article class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-zinc-50 sm:rounded-[1.6rem] dark:border-white/10 dark:bg-white/5">
                            <div class="grid min-w-0 gap-0 lg:grid-cols-[220px_minmax(0,1fr)]">
                                <div class="h-64 bg-zinc-200 lg:h-auto dark:bg-zinc-900">
                                    @if ($activeUnitMedia)
                                        <div class="relative h-full">
                                            <img src="{{ $activeUnitMedia->getUrl() }}" alt="{{ $unit->unit_name }} image {{ $activeUnitImage + 1 }}" class="h-full w-full object-cover" />

                                            @if ($unitGallery->count() > 1)
                                                <div class="absolute inset-x-3 top-1/2 flex -translate-y-1/2 items-center justify-between">
                                                    <button type="button" wire:click="previousUnitImage({{ $unit->id }})" class="inline-flex size-8 items-center justify-center rounded-full bg-zinc-950/70 text-white shadow-lg ring-1 ring-white/20 transition hover:bg-zinc-950" aria-label="Previous {{ $unit->unit_name }} image">
                                                        <span aria-hidden="true">&lsaquo;</span>
                                                    </button>
                                                    <button type="button" wire:click="nextUnitImage({{ $unit->id }})" class="inline-flex size-8 items-center justify-center rounded-full bg-zinc-950/70 text-white shadow-lg ring-1 ring-white/20 transition hover:bg-zinc-950" aria-label="Next {{ $unit->unit_name }} image">
                                                        <span aria-hidden="true">&rsaquo;</span>
                                                    </button>
                                                </div>
                                                <div class="absolute bottom-3 left-3 rounded-full bg-zinc-950/75 px-2.5 py-1 text-[11px] font-semibold text-white ring-1 ring-white/15">
                                                    {{ $activeUnitImage + 1 }} / {{ $unitGallery->count() }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="flex h-full items-end bg-[linear-gradient(135deg,#155e75,#0f766e,#022c22)] p-5 text-white">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.16em] text-white/65 sm:tracking-[0.2em]">Unit</p>
                                                <p class="mt-2 break-words text-2xl font-semibold">{{ $unit->unit_name }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 p-4 sm:p-5 lg:p-6">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <h3 class="break-words text-xl font-semibold text-zinc-950 dark:text-white">{{ $unit->unit_name }}</h3>
                                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $unit->unit_type }} {{ $unit->floor_label ? ' - '.$unit->floor_label : '' }}</p>
                                        </div>
                                        <span class="w-fit rounded-full bg-white px-3 py-1 text-xs font-semibold text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white">{{ $unit->occupancy_status->label() }}</span>
                                    </div>

                                    <div class="mt-5 grid gap-3 text-sm text-zinc-600 sm:grid-cols-4 dark:text-zinc-300">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">Bedrooms</p>
                                            <p class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ $unit->bedrooms }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">Bathrooms</p>
                                            <p class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ $unit->bathrooms }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">Size</p>
                                            <p class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $unit->size_sqm, 0) }} sqm</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">Billing</p>
                                            <p class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ $unit->billing_cycle->label() }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                        <article class="min-w-0 rounded-2xl bg-white px-4 py-3 shadow-sm dark:bg-zinc-950/70">
                                            <p class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">Rent</p>
                                            <p class="mt-2 break-words text-lg font-semibold text-zinc-950 dark:text-white">NGN {{ number_format((float) $unit->rent_amount) }}</p>
                                        </article>
                                        <article class="min-w-0 rounded-2xl bg-white px-4 py-3 shadow-sm dark:bg-zinc-950/70">
                                            <p class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">Service charge</p>
                                            <p class="mt-2 break-words text-lg font-semibold text-zinc-950 dark:text-white">NGN {{ number_format((float) $unit->service_charge_amount) }}</p>
                                        </article>
                                        <article class="min-w-0 rounded-2xl bg-white px-4 py-3 shadow-sm dark:bg-zinc-950/70">
                                            <p class="text-xs uppercase tracking-[0.14em] text-zinc-500 sm:tracking-[0.18em] dark:text-zinc-400">Available from</p>
                                            <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $unit->available_from?->format('M j, Y') ?? 'Now' }}</p>
                                        </article>
                                    </div>

                                    <p class="mt-5 text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $unit->description }}</p>

                                    @if ($unit->amenities->isNotEmpty())
                                        <div class="mt-5 flex flex-wrap gap-2">
                                            @foreach ($unit->amenities as $amenity)
                                                <span class="rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-medium text-zinc-700 dark:border-white/10 dark:bg-zinc-950/60 dark:text-zinc-200">{{ $amenity->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($unitGallery->count() > 1)
                                        <div class="mt-5 flex gap-2 overflow-x-auto pb-1">
                                            @foreach ($unitGallery as $index => $media)
                                                <button type="button" wire:click="showUnitImage({{ $unit->id }}, {{ $index }})" class="h-14 w-20 shrink-0 overflow-hidden rounded-xl border transition {{ $activeUnitImage === $index ? 'border-cyan-500 ring-2 ring-cyan-500/30' : 'border-zinc-200 opacity-75 hover:opacity-100 dark:border-white/10' }}" aria-label="Show {{ $unit->unit_name }} image {{ $index + 1 }}">
                                                    <img src="{{ $media->getUrl() }}" alt="{{ $unit->unit_name }} thumbnail {{ $index + 1 }}" class="h-full w-full object-cover">
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>

        <aside class="min-w-0 space-y-6">
            <livewire:marketplace.inspection-request-form :property="$property" />
            <livewire:marketplace.rental-application-form :property="$property" />

            <section class="rounded-3xl border border-white/70 bg-white/85 p-4 shadow-xl shadow-cyan-950/5 sm:rounded-[2rem] sm:p-6 dark:border-white/10 dark:bg-white/6">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Property Amenities</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($property->amenities as $amenity)
                        <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-medium text-zinc-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-200">{{ $amenity->name }}</span>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl border border-zinc-950 bg-zinc-950 p-4 text-white shadow-xl shadow-zinc-950/20 sm:rounded-[2rem] sm:p-6 dark:border-white/10 dark:bg-white dark:text-zinc-950">
                <p class="text-xs uppercase tracking-[0.16em] text-white/65 sm:tracking-[0.24em] dark:text-zinc-500">Need help deciding?</p>
                <h2 class="mt-3 text-xl font-semibold sm:text-2xl">Book a viewing before you apply.</h2>
                <p class="mt-3 text-sm leading-6 text-white/70 dark:text-zinc-600">Request an inspection slot, compare the available units, and submit an application when you are ready.</p>
            </section>
        </aside>
    </div>
</div>
