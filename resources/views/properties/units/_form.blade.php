@csrf

<div class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
    <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Unit Basics</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Unit Name</label><input type="text" name="unit_name" value="{{ old('unit_name', $unit->unit_name) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" required></div>
            <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Unit Code</label><input type="text" name="unit_code" value="{{ old('unit_code', $unit->unit_code) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
            <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Unit Type</label><input type="text" name="unit_type" value="{{ old('unit_type', $unit->unit_type) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
            <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Floor Label</label><input type="text" name="floor_label" value="{{ old('floor_label', $unit->floor_label) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
            <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Occupancy Status</label><select name="occupancy_status" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@foreach ($occupancyStatuses as $status)<option value="{{ $status->value }}" @selected(old('occupancy_status', $unit->occupancy_status?->value ?? 'vacant') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
            <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Available From</label><input type="date" name="available_from" value="{{ old('available_from', $unit->available_from?->format('Y-m-d')) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
            <div class="md:col-span-2"><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Description</label><textarea name="description" rows="4" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">{{ old('description', $unit->description) }}</textarea></div>
        </div>
    </section>

    <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Commercials and Media</h2>
        <div class="mt-5 grid gap-5">
            <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Billing Cycle</label><select name="billing_cycle" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">@foreach ($billingCycles as $cycle)<option value="{{ $cycle->value }}" @selected(old('billing_cycle', $unit->billing_cycle?->value ?? 'yearly') === $cycle->value)>{{ $cycle->label() }}</option>@endforeach</select></div>
            <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Rent Amount</label><input type="number" step="0.01" name="rent_amount" value="{{ old('rent_amount', $unit->rent_amount) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" required></div>
            <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Gallery Images</label><input type="file" name="media[]" multiple accept="image/*" class="mt-2 w-full rounded-2xl border border-dashed border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
            <label class="inline-flex items-center gap-3 rounded-2xl border border-zinc-200 px-4 py-3 text-sm dark:border-white/10"><input type="checkbox" name="is_listed" value="1" @checked(old('is_listed', $unit->is_listed ?? true))><span>Show this unit on public listings when available</span></label>
        </div>
    </section>
</div>

<section class="mt-6 rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Layout and Charges</h2>
    <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Bedrooms</label><input type="number" name="bedrooms" value="{{ old('bedrooms', $unit->bedrooms) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Bathrooms</label><input type="number" name="bathrooms" value="{{ old('bathrooms', $unit->bathrooms) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Toilets</label><input type="number" name="toilets" value="{{ old('toilets', $unit->toilets) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Size (sqm)</label><input type="number" step="0.01" name="size_sqm" value="{{ old('size_sqm', $unit->size_sqm) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Service Charge</label><input type="number" step="0.01" name="service_charge_amount" value="{{ old('service_charge_amount', $unit->service_charge_amount) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Caution Fee</label><input type="number" step="0.01" name="caution_fee_amount" value="{{ old('caution_fee_amount', $unit->caution_fee_amount) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Inspection Fee</label><input type="number" step="0.01" name="inspection_fee_amount" value="{{ old('inspection_fee_amount', $unit->inspection_fee_amount) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
    </div>

    <div class="mt-6">
        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Amenities</label>
        <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($amenities as $amenity)
                <label class="inline-flex items-center gap-3 rounded-2xl border border-zinc-200 px-4 py-3 text-sm dark:border-white/10"><input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id, $selectedAmenities, true))><span>{{ $amenity->name }}</span></label>
            @empty
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No amenities seeded yet.</p>
            @endforelse
        </div>
    </div>

    @if ($unit->exists && $unit->media->isNotEmpty())
        <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($unit->media as $item)
                <img src="{{ $item->getUrl() }}" alt="{{ $unit->unit_name }} image" class="h-36 w-full rounded-3xl object-cover">
            @endforeach
        </div>
    @endif

    <div class="mt-6"><button type="submit" class="rounded-full bg-zinc-950 px-5 py-2.5 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">{{ $submitLabel }}</button></div>
</section>
