<x-layouts.app>
    <div class="space-y-6">
        <div>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Inventory</p>
            <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Edit {{ $unit->unit_name }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Update occupancy, pricing, and media for this unit in {{ $property->title }}.</p>
        </div>

        <form method="POST" action="{{ route('properties.units.update', [$property, $unit]) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('properties.units._form', ['submitLabel' => 'Save Unit'])
        </form>
    </div>
</x-layouts.app>
