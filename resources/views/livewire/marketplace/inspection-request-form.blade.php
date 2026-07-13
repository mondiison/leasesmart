<div class="min-w-0 rounded-3xl border border-zinc-950 bg-zinc-950 p-4 text-white shadow-xl shadow-zinc-950/20 sm:rounded-[2rem] sm:p-6 dark:border-white/10 dark:bg-white dark:text-zinc-950">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs uppercase tracking-[0.16em] text-white/65 sm:tracking-[0.24em] dark:text-zinc-500">Inspection Booking</p>
            <h2 class="mt-3 text-xl font-semibold sm:text-2xl">Request a property viewing</h2>
            <p class="mt-3 text-sm leading-6 text-white/75 dark:text-zinc-600">Send your preferred slot and the operations team can confirm, reschedule, or close the request from the internal inspections queue.</p>
        </div>
    </div>

    <form wire:submit="save" class="mt-6 space-y-4">
        <div>
            <label for="inspection-unit" class="mb-2 block text-sm font-medium text-white dark:text-zinc-900">Unit</label>
            <select id="inspection-unit" wire:model.live="property_unit_id" required class="block w-full min-w-0 truncate rounded-2xl border border-white/15 bg-white/8 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 dark:border-zinc-300 dark:bg-white dark:text-zinc-950">
                @foreach ($propertyRecord->publicUnits as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->unit_name }} - {{ $unit->unit_type }} - NGN {{ number_format((float) $unit->rent_amount) }}</option>
                @endforeach
            </select>
            @error('property_unit_id') <p class="mt-2 text-sm text-rose-300 dark:text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="inspection-name" class="mb-2 block text-sm font-medium text-white dark:text-zinc-900">Full name</label>
                <input id="inspection-name" type="text" wire:model.live.debounce.300ms="requester_name" required class="block w-full min-w-0 rounded-2xl border border-white/15 bg-white/8 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 dark:border-zinc-300 dark:bg-white dark:text-zinc-950" />
                @error('requester_name') <p class="mt-2 text-sm text-rose-300 dark:text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="inspection-phone" class="mb-2 block text-sm font-medium text-white dark:text-zinc-900">Phone</label>
                <input id="inspection-phone" type="text" wire:model.live.debounce.300ms="requester_phone" required class="block w-full min-w-0 rounded-2xl border border-white/15 bg-white/8 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 dark:border-zinc-300 dark:bg-white dark:text-zinc-950" />
                @error('requester_phone') <p class="mt-2 text-sm text-rose-300 dark:text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="inspection-email" class="mb-2 block text-sm font-medium text-white dark:text-zinc-900">Email</label>
            <input id="inspection-email" type="email" wire:model.live.debounce.300ms="requester_email" required class="block w-full min-w-0 rounded-2xl border border-white/15 bg-white/8 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 dark:border-zinc-300 dark:bg-white dark:text-zinc-950" />
            @error('requester_email') <p class="mt-2 text-sm text-rose-300 dark:text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="inspection-date" class="mb-2 block text-sm font-medium text-white dark:text-zinc-900">Preferred date</label>
                <input id="inspection-date" type="date" wire:model.blur="requested_for_date" class="block w-full min-w-0 rounded-2xl border border-white/15 bg-white/8 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 dark:border-zinc-300 dark:bg-white dark:text-zinc-950" />
                @error('requested_for_date') <p class="mt-2 text-sm text-rose-300 dark:text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="inspection-time" class="mb-2 block text-sm font-medium text-white dark:text-zinc-900">Preferred time</label>
                <input id="inspection-time" type="time" wire:model.blur="requested_for_time" class="block w-full min-w-0 rounded-2xl border border-white/15 bg-white/8 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 dark:border-zinc-300 dark:bg-white dark:text-zinc-950" />
                @error('requested_for_time') <p class="mt-2 text-sm text-rose-300 dark:text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="inspection-message" class="mb-2 block text-sm font-medium text-white dark:text-zinc-900">Message</label>
            <textarea id="inspection-message" rows="4" wire:model.blur="message" class="block w-full min-w-0 rounded-2xl border border-white/15 bg-white/8 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 dark:border-zinc-300 dark:bg-white dark:text-zinc-950"></textarea>
            @error('message') <p class="mt-2 text-sm text-rose-300 dark:text-rose-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" @disabled(! $this->canSubmit) wire:loading.attr="disabled" class="w-full rounded-full bg-white px-5 py-3 text-sm font-semibold text-zinc-950 transition hover:bg-zinc-100 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto dark:bg-zinc-950 dark:text-white dark:hover:bg-zinc-800">Submit inspection request</button>
    </form>
</div>
