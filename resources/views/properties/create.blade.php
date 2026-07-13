<x-layouts.app>
    <div class="space-y-6">
        <div>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Inventory</p>
            <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Create Property</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Set up the parent asset, assign ownership, and capture listing readiness details.</p>
        </div>

        <form method="POST" action="{{ route('properties.store') }}" enctype="multipart/form-data">
            @include('properties._form', ['submitLabel' => 'Create Property'])
        </form>
    </div>
</x-layouts.app>
