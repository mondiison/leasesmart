<div class="min-w-0 rounded-3xl border border-white/70 bg-white/85 p-4 shadow-xl shadow-cyan-950/5 sm:rounded-[2rem] sm:p-6 dark:border-white/10 dark:bg-white/6">
    <div class="min-w-0">
        <p class="text-xs uppercase tracking-[0.16em] text-zinc-500 sm:tracking-[0.24em] dark:text-zinc-400">Rental Application</p>
        <h2 class="mt-3 text-xl font-semibold text-zinc-950 sm:text-2xl dark:text-white">Apply for this property</h2>
        <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">Share your move-in timeline, employment context, and supporting documents so the review team can make a faster decision.</p>
    </div>

    <form wire:submit="save" class="mt-6 space-y-4">
        <div>
            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Unit</label>
            <select wire:model.live="property_unit_id" required class="block w-full min-w-0 truncate rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
                @foreach ($propertyRecord->publicUnits as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->unit_name }} - {{ $unit->unit_type }} - NGN {{ number_format((float) $unit->rent_amount) }}</option>
                @endforeach
            </select>
            @error('property_unit_id') <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
        </div>

        @if ($inspectionOptions->isNotEmpty())
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Link prior inspection</label>
                <select wire:model="inspection_id" class="block w-full min-w-0 truncate rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
                    <option value="">No linked inspection</option>
                    @foreach ($inspectionOptions as $inspectionOption)
                        <option value="{{ $inspectionOption->id }}">{{ $inspectionOption->property->title }} - {{ $inspectionOption->unit?->unit_name ?? 'Unit' }} - {{ $inspectionOption->status->label() }}</option>
                    @endforeach
                </select>
                @error('inspection_id') <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Full name</label>
                <input type="text" wire:model.live.debounce.300ms="applicant_name" required class="block w-full min-w-0 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white" />
                @error('applicant_name') <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Phone</label>
                <input type="text" wire:model.live.debounce.300ms="applicant_phone" required class="block w-full min-w-0 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white" />
                @error('applicant_phone') <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Email</label>
            <input type="email" wire:model.live.debounce.300ms="applicant_email" required class="block w-full min-w-0 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white" />
            @error('applicant_email') <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Employment status</label>
                <select wire:model.blur="employment_status" class="block w-full min-w-0 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white">
                    <option value="">Select status</option>
                    @foreach ($employmentStatuses as $employmentOption)
                        <option value="{{ $employmentOption }}">{{ $employmentOption }}</option>
                    @endforeach
                </select>
                @error('employment_status') <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Employer name</label>
                <input type="text" wire:model.blur="employer_name" class="block w-full min-w-0 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white" />
                @error('employer_name') <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Monthly income</label>
                <input type="number" step="0.01" min="0" wire:model.blur="monthly_income" class="block w-full min-w-0 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white" />
                @error('monthly_income') <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Preferred move-in date</label>
                <input type="date" wire:model.blur="preferred_move_in_date" class="block w-full min-w-0 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white" />
                @error('preferred_move_in_date') <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <flux:file-upload wire:model="documents" multiple label="Supporting documents" description="Accepted: PDF, JPG, PNG, DOC, DOCX up to 5MB each.">
                <flux:file-upload.dropzone
                    heading="Drop documents here or click to browse"
                    text="PDF, JPG, PNG, DOC, DOCX up to 5MB"
                    with-progress
                    inline
                />
            </flux:file-upload>
            @error('documents.*') <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror

            @if ($documents !== [])
                <div class="mt-3 flex flex-col gap-2">
                    @foreach ($documents as $index => $file)
                        <flux:file-item
                            :heading="$file->getClientOriginalName()"
                            :image="str_starts_with($file->getMimeType(), 'image/') ? $file->temporaryUrl() : null"
                            :icon="str_starts_with($file->getMimeType(), 'image/') ? null : 'document'"
                            :size="$file->getSize()"
                        >
                            <x-slot name="actions">
                                <flux:file-item.remove wire:click="removeDocumentUpload({{ $index }})" aria-label="Remove {{ $file->getClientOriginalName() }}" />
                            </x-slot>
                        </flux:file-item>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Application note</label>
            <textarea rows="4" wire:model.blur="message" class="block w-full min-w-0 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950/60 dark:text-white"></textarea>
            @error('message') <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p> @enderror
        </div>

        <button type="submit" @disabled(! $this->canSubmit) wire:loading.attr="disabled" class="w-full rounded-full bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100">Submit rental application</button>
    </form>
</div>
