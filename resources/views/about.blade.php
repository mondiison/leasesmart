<x-layouts.marketing>
    <div class="pb-16">
        <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
            <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" wire:navigate class="font-medium text-zinc-700 transition hover:text-sky-700 dark:text-zinc-300 dark:hover:text-sky-200">Home</a>
                <span aria-hidden="true">/</span>
                <span class="font-medium text-zinc-950 dark:text-white" aria-current="page">About</span>
            </nav>

            <div class="rounded-[2rem] bg-[#fbfcfc] p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:p-8 lg:p-10 dark:bg-zinc-900">
                <div class="grid gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(340px,0.95fr)] lg:items-center">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.26em] text-sky-700 dark:text-sky-300">Built for clearer leasing</p>
                        <h1 class="mt-5 max-w-4xl text-4xl font-semibold leading-[1.02] tracking-tight text-zinc-950 sm:text-6xl dark:text-white">
                            A calmer way to find, lease, and manage premium homes.
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-7 text-zinc-600 sm:text-lg dark:text-zinc-300">
                            LeaseSmart Premium brings verified rental discovery and day-to-day property operations into one connected experience, so renters can move with confidence and property teams can lease with less friction.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('marketplace.index') }}" wire:navigate class="inline-flex justify-center rounded-full bg-sky-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-900/15 transition hover:bg-sky-700">Browse Listings</a>
                            @auth
                                <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex justify-center rounded-full border border-zinc-300/80 bg-white px-6 py-3 text-sm font-semibold text-zinc-900 transition hover:border-zinc-950 dark:border-white/15 dark:bg-white/5 dark:text-white dark:hover:border-white/40">Open Dashboard</a>
                            @else
                                <a href="{{ route('register') }}" wire:navigate class="inline-flex justify-center rounded-full border border-zinc-300/80 bg-white px-6 py-3 text-sm font-semibold text-zinc-900 transition hover:border-zinc-950 dark:border-white/15 dark:bg-white/5 dark:text-white dark:hover:border-white/40">List With Us</a>
                            @endauth
                        </div>
                    </div>

                    <div class="rounded-[1.6rem] bg-white/70 p-3 shadow-[0_18px_48px_rgba(15,23,42,0.06)] dark:bg-white/5">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <article class="rounded-[1.25rem] bg-white px-4 py-5 shadow-[0_14px_35px_rgba(15,23,42,0.055)] dark:bg-white/6">
                                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">Verified</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Published inventory is curated around real availability, location clarity, amenities, and viewing readiness.</p>
                            </article>
                            <article class="rounded-[1.25rem] bg-white px-4 py-5 shadow-[0_14px_35px_rgba(15,23,42,0.055)] dark:bg-white/6">
                                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">Connected</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Inspections, applications, tenancies, invoices, payments, and support stay close to the property record.</p>
                            </article>
                            <article class="rounded-[1.25rem] bg-white px-4 py-5 shadow-[0_14px_35px_rgba(15,23,42,0.055)] dark:bg-white/6 sm:col-span-2">
                                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">Human-first</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Every workflow is designed to reduce guesswork: what is available, what it costs, what happens next, and who needs to act.</p>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto mt-6 grid max-w-7xl gap-5 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <article class="rounded-[1.5rem] bg-white p-6 shadow-[0_20px_55px_rgba(15,23,42,0.08)] dark:bg-zinc-900/70">
                <div class="grid size-11 place-items-center rounded-full bg-sky-50 text-sky-700 dark:bg-sky-300/10 dark:text-sky-200">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11s-8-6-8-11a8 8 0 1 1 16 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
                    </svg>
                </div>
                <h2 class="mt-5 text-xl font-semibold tracking-tight text-zinc-950 dark:text-white">Search With Context</h2>
                <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Renters can compare homes by city, budget, bedrooms, amenities, photos, and published unit status before committing time to a viewing.</p>
            </article>

            <article class="rounded-[1.5rem] bg-white p-6 shadow-[0_20px_55px_rgba(15,23,42,0.08)] dark:bg-zinc-900/70">
                <div class="grid size-11 place-items-center rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-300/10 dark:text-emerald-200">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 4v5c0 5-3.4 8.6-8 9-4.6-.4-8-4-8-9V7l8-4Z" />
                    </svg>
                </div>
                <h2 class="mt-5 text-xl font-semibold tracking-tight text-zinc-950 dark:text-white">Lease With Confidence</h2>
                <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Inspection requests and rental applications flow from the public listing into the operating workspace, keeping follow-up clear and timely.</p>
            </article>

            <article class="rounded-[1.5rem] bg-white p-6 shadow-[0_20px_55px_rgba(15,23,42,0.08)] dark:bg-zinc-900/70">
                <div class="grid size-11 place-items-center rounded-full bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 21V6l8-3 8 3v15" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 21v-7h6v7M8 8h.01M16 8h.01" />
                    </svg>
                </div>
                <h2 class="mt-5 text-xl font-semibold tracking-tight text-zinc-950 dark:text-white">Manage Beyond Move-In</h2>
                <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Tenancy records, invoices, payments, documents, and maintenance requests help teams keep the relationship organized after a lease starts.</p>
            </article>
        </section>

        <section class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-5 rounded-[1.8rem] bg-white p-5 shadow-[0_24px_70px_rgba(15,23,42,0.08)] lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] lg:p-8 dark:bg-zinc-900/70">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-700 dark:text-sky-300">Our point of view</p>
                    <h2 class="mt-4 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">Premium leasing should feel precise, transparent, and easy to follow.</h2>
                    <p class="mt-4 text-sm leading-6 text-zinc-600 dark:text-zinc-300">The best rental platforms make the path obvious for both sides: renters need trustworthy information and fast next steps, while owners need clean data, fewer manual handoffs, and a reliable record of every action.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-[1.25rem] bg-zinc-50 p-5 dark:bg-white/6">
                        <h3 class="font-semibold text-zinc-950 dark:text-white">Clarity over clutter</h3>
                        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Listings focus on the details that influence real decisions: price, location, unit mix, availability, amenities, and viewing options.</p>
                    </div>
                    <div class="rounded-[1.25rem] bg-zinc-50 p-5 dark:bg-white/6">
                        <h3 class="font-semibold text-zinc-950 dark:text-white">Trust at every step</h3>
                        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Every public inquiry becomes a trackable workflow instead of a loose message that can be missed or duplicated.</p>
                    </div>
                    <div class="rounded-[1.25rem] bg-zinc-50 p-5 dark:bg-white/6">
                        <h3 class="font-semibold text-zinc-950 dark:text-white">Built for teams</h3>
                        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Admins, landlords, caretakers, tenants, and applicants each get access to the work that belongs to them.</p>
                    </div>
                    <div class="rounded-[1.25rem] bg-zinc-50 p-5 dark:bg-white/6">
                        <h3 class="font-semibold text-zinc-950 dark:text-white">Ready to grow</h3>
                        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">The platform keeps marketplace, back-office, reporting, and API foundations in one place as portfolios expand.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-[1.8rem] bg-zinc-950 p-6 text-white shadow-[0_24px_70px_rgba(15,23,42,0.16)] sm:p-8 lg:flex lg:items-center lg:justify-between lg:gap-8 dark:bg-white dark:text-zinc-950">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-300 dark:text-sky-700">Start where you are</p>
                    <h2 class="mt-4 max-w-2xl text-3xl font-semibold tracking-tight">Find a home, publish better inventory, or run the leasing desk from one workspace.</h2>
                </div>
                <div class="mt-6 flex flex-wrap gap-3 lg:mt-0">
                    <a href="{{ route('marketplace.index') }}" wire:navigate class="inline-flex justify-center rounded-full bg-white px-5 py-3 text-sm font-semibold text-zinc-950 transition hover:bg-zinc-100 dark:bg-zinc-950 dark:text-white dark:hover:bg-zinc-800">Explore Listings</a>
                    @guest
                        <a href="{{ route('login') }}" wire:navigate class="inline-flex justify-center rounded-full border border-white/20 px-5 py-3 text-sm font-semibold text-white transition hover:border-white/50 dark:border-zinc-300 dark:text-zinc-950 dark:hover:border-zinc-950">Sign In</a>
                    @endguest
                </div>
            </div>
        </section>
    </div>
</x-layouts.marketing>
