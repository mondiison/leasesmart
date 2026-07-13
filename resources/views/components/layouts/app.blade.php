<x-layouts.app.sidebar>
    <flux:main class="bg-[radial-gradient(circle_at_top,#dff7f5,transparent_40%),linear-gradient(to_bottom,#f7faf9,#ffffff)] dark:bg-[radial-gradient(circle_at_top,#11303a,transparent_30%),linear-gradient(to_bottom,#101826,#0a0f19)]">
        <div class="mx-auto w-full max-w-7xl !px-4 !py-6 sm:!px-6 lg:!px-8">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts.app.sidebar>
