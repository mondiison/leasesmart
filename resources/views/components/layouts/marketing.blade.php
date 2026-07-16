<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#f7faf9] text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
        <div class="relative min-h-screen overflow-x-hidden">
            <header class="sticky top-0 z-40 border-b border-zinc-200/70 bg-white/88 backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/75">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
                        <x-app-logo />
                    </a>

                    <nav class="hidden items-center gap-6 text-sm text-zinc-600 md:flex dark:text-zinc-300">
                        <a href="{{ route('home') }}" wire:navigate class="transition hover:text-zinc-950 dark:hover:text-white">Home</a>
                        <a href="{{ route('marketplace.index') }}" wire:navigate class="transition hover:text-zinc-950 dark:hover:text-white">Listings</a>
                        <a href="{{ route('about') }}" wire:navigate class="transition hover:text-zinc-950 dark:hover:text-white">About</a>
                    </nav>

                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate class="rounded-full border border-zinc-300/80 px-4 py-2 text-sm font-medium text-zinc-900 transition hover:border-zinc-950 dark:border-white/15 dark:text-white dark:hover:border-white/40">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="text-sm font-medium text-zinc-700 transition hover:text-zinc-950 dark:text-zinc-300 dark:hover:text-white">Log in</a>
                            <a href="{{ route('register') }}" wire:navigate class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100">Get Started</a>
                        @endauth
                    </div>

                    <nav class="flex w-full items-center gap-5 border-t border-zinc-200/70 pt-3 text-sm text-zinc-600 md:hidden dark:border-white/10 dark:text-zinc-300">
                        <a href="{{ route('home') }}" wire:navigate class="transition hover:text-zinc-950 dark:hover:text-white">Home</a>
                        <a href="{{ route('marketplace.index') }}" wire:navigate class="transition hover:text-zinc-950 dark:hover:text-white">Listings</a>
                        <a href="{{ route('about') }}" wire:navigate class="transition hover:text-zinc-950 dark:hover:text-white">About</a>
                    </nav>
                </div>
            </header>

            <main>
                {{ $slot }}
            </main>

            <footer class="border-t border-white/60 bg-white/60 py-8 dark:border-white/10 dark:bg-zinc-950/50">
                <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 text-sm text-zinc-600 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8 dark:text-zinc-400">
                    <p>LeaseSmart Premium helps rental teams move from inquiry to occupancy with cleaner inventory operations.</p>
                    <p>{{ now()->year }} LeaseSmart Premium</p>
                </div>
            </footer>
        </div>

        <flux:toast.group position="top end">
            <flux:toast />
        </flux:toast.group>

        @fluxScripts

        @if (session('flux.toast'))
            <script>
                queueMicrotask(() => window.dispatchEvent(new CustomEvent('toast-show', { detail: @js(session('flux.toast')) })));
            </script>
        @endif
    </body>
</html>
