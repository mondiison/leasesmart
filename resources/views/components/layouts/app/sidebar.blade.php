<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    @php
        $user = auth()->user();
        $navigation = \App\Support\Navigation\AppNavigation::for($user);
        $avatarUrl = $user->avatarUrl();
        $unreadNotificationCount = $user->unreadNotifications()->count();
    @endphp
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-zinc-950 dark:bg-zinc-950 dark:text-white">
        <flux:sidebar sticky stashable class="border-r border-white/60 bg-white/90 backdrop-blur dark:border-white/10 dark:bg-zinc-950/90">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard', absolute: false) }}" class="mr-5 flex items-center space-x-2" wire:navigate>
                <x-app-logo class="size-8" href="#"></x-app-logo>
            </a>

            <form method="GET" action="{{ route('search.index') }}" class="mt-5">
                <label for="global-shell-search" class="sr-only">Search</label>
                <input id="global-shell-search" type="search" name="q" value="{{ request('q') }}" placeholder="Find anything" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white">
            </form>

            <a href="{{ route('notifications.index') }}" wire:navigate class="mt-5 flex items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-900 shadow-sm transition hover:border-cyan-300 hover:text-cyan-800 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-cyan-400/40">
                <span class="flex items-center gap-3">
                    <span class="relative inline-flex h-9 w-9 items-center justify-center rounded-full bg-zinc-950 text-white dark:bg-white dark:text-zinc-950">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.268 21a2 2 0 0 0 3.464 0" />
                            <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326" />
                        </svg>
                        @if ($unreadNotificationCount > 0)
                            <span class="absolute -right-1 -top-1 h-3 w-3 rounded-full border-2 border-white bg-cyan-500 dark:border-zinc-950"></span>
                        @endif
                    </span>
                    <span>Notifications</span>
                </span>
                <span class="rounded-full bg-cyan-100 px-2.5 py-1 text-xs font-semibold text-cyan-900 dark:bg-cyan-300 dark:text-cyan-950">{{ number_format($unreadNotificationCount) }}</span>
            </a>

            <div class="mt-5 rounded-3xl border border-emerald-200/70 bg-emerald-50/80 p-4 text-sm text-emerald-950 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-100">
                <p class="font-semibold">{{ $user->roleLabel() }}</p>
                <p class="mt-1 text-xs leading-5 text-emerald-800 dark:text-emerald-200/80">
                    {{ $user->primaryRole()?->description() ?? 'Signed-in workspace ready for module delivery.' }}
                </p>
            </div>

            <flux:navlist variant="outline" class="mt-6">
                @foreach ($navigation as $group)
                    <flux:navlist.group :heading="$group['heading']" class="grid">
                        @foreach ($group['items'] as $item)
                            <flux:navlist.item :icon="$item['icon']" :href="$item['href']" :current="$item['current']" wire:navigate>
                                {{ $item['label'] }}
                            </flux:navlist.item>
                        @endforeach
                    </flux:navlist.group>
                @endforeach
            </flux:navlist>

            <flux:spacer />

            <div class="rounded-3xl border border-cyan-200/70 bg-cyan-50/80 p-4 text-sm text-cyan-950 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-100">
                <p class="font-medium">Workspace Status</p>
                <p class="mt-1 text-xs leading-5 text-cyan-800 dark:text-cyan-200/80">
                    Property, leasing, billing, and maintenance workflows are ready for day-to-day rental operations.
                </p>
            </div>

            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="$user->name"
                    :initials="$user->initials()"
                    :avatar="$avatarUrl"
                    icon-trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    @if ($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="h-full w-full rounded-lg object-cover">
                                    @else
                                        <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {{ $user->initials() }}
                                        </span>
                                    @endif
                                </span>

                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ $user->name }}</span>
                                    <span class="truncate text-xs">{{ $user->email }}</span>
                                    <span class="truncate text-[11px] uppercase tracking-[0.18em] text-zinc-500">{{ $user->roleLabel() }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <a href="{{ route('notifications.index') }}" wire:navigate class="relative mr-3 inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-900 dark:border-white/10 dark:bg-white/5 dark:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.268 21a2 2 0 0 0 3.464 0" />
                    <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326" />
                </svg>
                @if ($unreadNotificationCount > 0)
                    <span class="absolute -right-1 -top-1 min-w-5 rounded-full bg-cyan-600 px-1.5 py-0.5 text-center text-[10px] font-semibold leading-none text-white">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                @endif
            </a>

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="$user->initials()"
                    :avatar="$avatarUrl"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    @if ($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="h-full w-full rounded-lg object-cover">
                                    @else
                                        <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {{ $user->initials() }}
                                        </span>
                                    @endif
                                </span>

                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ $user->name }}</span>
                                    <span class="truncate text-xs">{{ $user->email }}</span>
                                    <span class="truncate text-[11px] uppercase tracking-[0.18em] text-zinc-500">{{ $user->roleLabel() }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

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
