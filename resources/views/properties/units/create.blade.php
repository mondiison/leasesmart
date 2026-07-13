<x-layouts.app>
    <div class="space-y-6">
        <div>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Inventory</p>
            <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">New Unit for {{ $property->title }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Define the rentable unit, its pricing model, and listing metadata.</p>
        </div>

        <form method="POST" action="{{ route('properties.units.store', $property) }}" enctype="multipart/form-data">
            @include('properties.units._form', ['submitLabel' => 'Create Unit'])
        </form>
    </div>
</x-layouts.app>
