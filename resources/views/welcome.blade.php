<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>LeaseSmart Premium</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|space-grotesk:500,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[linear-gradient(180deg,#f5fbff_0%,#eef8f4_40%,#ffffff_100%)] text-zinc-950 dark:bg-zinc-950 dark:text-white">
        <div class="relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 -z-10 h-[36rem] bg-[radial-gradient(circle_at_top_left,rgba(14,165,233,0.16),transparent_42%),radial-gradient(circle_at_top_right,rgba(16,185,129,0.16),transparent_35%)]"></div>

            <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <x-app-logo class="size-10" href="#"></x-app-logo>
                    <div>
                        <p class="font-['Space_Grotesk'] text-lg font-bold tracking-tight">LeaseSmart Premium</p>
                        <p class="text-xs uppercase tracking-[0.24em] text-zinc-500 dark:text-zinc-400">Rental Operations Platform</p>
                    </div>
                </a>

                <nav class="flex items-center gap-3">
                    <a href="{{ route('marketplace.index') }}" class="rounded-full border border-zinc-300 bg-white/80 px-4 py-2 text-sm font-semibold text-zinc-900 transition hover:border-zinc-950 dark:border-white/15 dark:bg-white/5 dark:text-white dark:hover:border-white/40">
                        Browse Listings
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200">
                            Open Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200">
                            Sign In
                        </a>
                    @endauth
                </nav>
            </header>

            <main class="mx-auto flex w-full max-w-7xl flex-col gap-14 px-6 pb-16 pt-8 lg:px-8 lg:pb-24 lg:pt-14">
                <section class="grid gap-10 lg:grid-cols-[minmax(0,1.2fr)_minmax(360px,0.8fr)] lg:items-center">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-cyan-700 dark:text-cyan-300">Marketplace, Operations, Billing, and Support</p>
                        <h1 class="mt-4 max-w-4xl font-['Space_Grotesk'] text-5xl font-bold tracking-tight text-zinc-950 dark:text-white sm:text-6xl">
                            One platform for listings, leasing, tenants, and day-to-day property operations.
                        </h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-zinc-600 dark:text-zinc-300">
                            LeaseSmart Premium brings public listing discovery together with internal property workflows, inspections, applications, tenancies, billing, maintenance, dashboards, and mobile-ready APIs.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('marketplace.index') }}" class="rounded-full bg-cyan-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                                Explore Marketplace
                            </a>
                            @auth
                                <a href="{{ route('dashboard') }}" class="rounded-full border border-zinc-300 bg-white px-6 py-3 text-sm font-semibold text-zinc-900 transition hover:border-zinc-950 dark:border-white/15 dark:bg-white/5 dark:text-white dark:hover:border-white/40">
                                    Go to Workspace
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="rounded-full border border-zinc-300 bg-white px-6 py-3 text-sm font-semibold text-zinc-900 transition hover:border-zinc-950 dark:border-white/15 dark:bg-white/5 dark:text-white dark:hover:border-white/40">
                                    Create Account
                                </a>
                            @endauth
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-lg shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Current Scope</p>
                                <p class="mt-2 text-2xl font-semibold tracking-tight">Phases 0-11</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Core roadmap delivered through marketplace, operations, API, and hardening.</p>
                            </div>
                            <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-lg shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Internal UX</p>
                                <p class="mt-2 text-2xl font-semibold tracking-tight">Livewire First</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Back-office workflows run with stateful Livewire screens and shared action classes.</p>
                            </div>
                            <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-lg shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Mobile Ready</p>
                                <p class="mt-2 text-2xl font-semibold tracking-tight">Sanctum API</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Authenticated account, tenancy, billing, and maintenance endpoints are available.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-2xl shadow-cyan-950/10 dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-cyan-700 dark:text-cyan-300">What Teams Can Do</p>
                        <div class="mt-6 space-y-4">
                            <article class="rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
                                <h2 class="text-lg font-semibold">Admins and Landlords</h2>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Manage users, inventory, applications, tenancies, billing, maintenance, dashboards, and reporting from one shared workspace.</p>
                            </article>
                            <article class="rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
                                <h2 class="text-lg font-semibold">Caretakers</h2>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Coordinate inspections, support work, and operational readiness with scoped access to assigned properties.</p>
                            </article>
                            <article class="rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50/80 p-5 dark:border-white/10 dark:bg-zinc-900/60">
                                <h2 class="text-lg font-semibold">Tenants and Applicants</h2>
                                <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Book inspections, apply for units, track tenancy, submit payments, and follow maintenance progress.</p>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 lg:grid-cols-4">
                    <article class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Marketplace</p>
                        <h2 class="mt-2 text-xl font-semibold tracking-tight">Searchable public listings</h2>
                        <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Published properties and visible units feed the public discovery experience with filters and SEO-friendly detail pages.</p>
                    </article>
                    <article class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Leasing</p>
                        <h2 class="mt-2 text-xl font-semibold tracking-tight">Inspections to applications</h2>
                        <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Viewing requests, rental applications, and tenancy creation now form one connected leasing pipeline.</p>
                    </article>
                    <article class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Operations</p>
                        <h2 class="mt-2 text-xl font-semibold tracking-tight">Billing and maintenance</h2>
                        <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Invoices, receipts, payment review, maintenance requests, updates, and resolution tracking are all active in-app.</p>
                    </article>
                    <article class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Readiness</p>
                        <h2 class="mt-2 text-xl font-semibold tracking-tight">API and hardening baseline</h2>
                        <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Sanctum endpoints, security headers, auth throttling, and health checks are in place for the next deployment step.</p>
                    </article>
                </section>
            </main>
        </div>
    </body>
</html>
