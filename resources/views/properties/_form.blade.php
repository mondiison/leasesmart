@csrf

<div class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
    <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Property Identity</h2>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Title</label>
                <input type="text" name="title" value="{{ old('title', $property->title) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" required>
                @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Property Code</label>
                <input type="text" name="property_code" value="{{ old('property_code', $property->property_code) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                @error('property_code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Property Type</label>
                <select name="property_type" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" required>
                    @foreach ($propertyTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('property_type', $property->property_type?->value) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Publish Status</label>
                <select name="publish_status" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" required>
                    @foreach ($publishStatuses as $status)
                        <option value="{{ $status->value }}" @selected(old('publish_status', $property->publish_status?->value ?? 'draft') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                @error('publish_status') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Description</label>
                <textarea name="description" rows="5" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">{{ old('description', $property->description) }}</textarea>
            </div>
        </div>
    </section>

    <section class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Assignments and Flags</h2>
        <div class="mt-5 grid gap-5">
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Landlord</label>
                <select name="landlord_id" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                    <option value="">Select landlord</option>
                    @foreach ($landlords as $landlord)
                        <option value="{{ $landlord->id }}" @selected((string) old('landlord_id', $property->landlord_id) === (string) $landlord->id)>{{ $landlord->user?->name ?? 'Landlord #'.$landlord->id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Caretaker</label>
                <select name="caretaker_id" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                    <option value="">Select caretaker</option>
                    @foreach ($caretakers as $caretaker)
                        <option value="{{ $caretaker->id }}" @selected((string) old('caretaker_id', $property->caretaker_id) === (string) $caretaker->id)>{{ $caretaker->user?->name ?? 'Caretaker #'.$caretaker->id }}</option>
                    @endforeach
                </select>
            </div>
            <label class="inline-flex items-center gap-3 rounded-2xl border border-zinc-200 px-4 py-3 text-sm dark:border-white/10">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $property->is_featured ?? false))>
                <span>Feature this property in future listing surfaces</span>
            </label>
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Gallery Images</label>
                <input type="file" name="media[]" multiple accept="image/*" class="mt-2 w-full rounded-2xl border border-dashed border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950">
                @error('media.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>
</div>

<section class="mt-6 rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Location and Amenities</h2>
    <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Address Line 1</label><input type="text" name="address_line_1" value="{{ old('address_line_1', $property->address_line_1) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" required></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Address Line 2</label><input type="text" name="address_line_2" value="{{ old('address_line_2', $property->address_line_2) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">City</label><input type="text" name="city" value="{{ old('city', $property->city) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" required></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">State</label><input type="text" name="state" value="{{ old('state', $property->state) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Country</label><input type="text" name="country" value="{{ old('country', $property->country ?? 'Nigeria') }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950" required></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Postal Code</label><input type="text" name="postal_code" value="{{ old('postal_code', $property->postal_code) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Latitude</label><input type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $property->latitude) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Longitude</label><input type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $property->longitude) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
        <div><label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Year Built</label><input type="number" name="year_built" value="{{ old('year_built', $property->year_built) }}" class="mt-2 w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm dark:border-white/10 dark:bg-zinc-950"></div>
    </div>

    <div class="mt-6">
        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Amenities</label>
        <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($amenities as $amenity)
                <label class="inline-flex items-center gap-3 rounded-2xl border border-zinc-200 px-4 py-3 text-sm dark:border-white/10">
                    <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id, $selectedAmenities, true))>
                    <span>{{ $amenity->name }}</span>
                </label>
            @empty
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No amenities seeded yet.</p>
            @endforelse
        </div>
    </div>

    @if ($property->exists && $property->media->isNotEmpty())
        <div class="mt-8">
            <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Current Gallery</h3>
            <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($property->media as $item)
                    <img src="{{ $item->getUrl() }}" alt="{{ $property->title }} image" class="h-40 w-full rounded-3xl object-cover">
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-6 flex flex-wrap items-center gap-3">
        <button type="submit" class="rounded-full bg-zinc-950 px-5 py-2.5 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">{{ $submitLabel }}</button>
        @if ($property->exists)
            <span class="text-sm text-zinc-500 dark:text-zinc-400">Slug: {{ $property->slug }}</span>
        @endif
    </div>
</section>
