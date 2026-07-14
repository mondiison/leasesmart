<div class="space-y-6">
    @php($isTenant = auth()->user()->hasRole('tenant'))

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Finance</p>
            <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $isTenant ? 'My Billing' : 'Billing' }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ $isTenant ? 'Review invoices, submit payment proof, and track verification or receipts from one place.' : 'Issue tenancy-backed invoices, capture payment submissions, verify incoming payments, and generate receipts from one workspace.' }}
            </p>
        </div>
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-3xl border border-zinc-200/80 bg-white/80 px-4 py-3 text-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-zinc-500 dark:text-zinc-400">Invoices</p>
                <p class="mt-1 text-xl font-semibold text-zinc-950 dark:text-white">{{ $invoices->count() }}</p>
            </div>
            <div class="rounded-3xl border border-zinc-200/80 bg-white/80 px-4 py-3 text-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-zinc-500 dark:text-zinc-400">Open Balance</p>
                <p class="mt-1 text-xl font-semibold text-zinc-950 dark:text-white">NGN {{ number_format((float) $invoices->sum('balance_amount'), 2) }}</p>
            </div>
            <div class="rounded-3xl border border-zinc-200/80 bg-white/80 px-4 py-3 text-sm dark:border-white/10 dark:bg-white/5">
                <p class="text-zinc-500 dark:text-zinc-400">Payments</p>
                <p class="mt-1 text-xl font-semibold text-zinc-950 dark:text-white">{{ $payments->count() }}</p>
            </div>
        </div>
    </div>

    <section class="grid gap-3 rounded-[1.25rem] border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/60 md:grid-cols-[minmax(0,1fr)_240px]">
        <div>
            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Search billing</label>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Invoice, payment reference, tenant, property" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Focus</label>
            <select wire:model.live="focus" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                <option value="">All billing</option>
                <option value="overdue">Overdue invoices</option>
                <option value="due_soon">Due within 7 days</option>
                <option value="pending_payments">Pending payments</option>
            </select>
        </div>
        <div class="flex flex-wrap gap-2 md:col-span-2">
            <a href="{{ route('exports.show', ['type' => 'billing-invoices', 'q' => $search, 'focus' => $focus]) }}" class="inline-flex rounded-full border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-900 transition hover:border-zinc-950 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/30">Export invoices CSV</a>
            <a href="{{ route('exports.show', ['type' => 'billing-payments', 'q' => $search, 'focus' => $focus]) }}" class="inline-flex rounded-full border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-900 transition hover:border-zinc-950 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:border-white/30">Export payments CSV</a>
            <a href="{{ route('reports.premium', ['type' => 'billing-invoices', 'q' => $search, 'focus' => $focus]) }}" target="_blank" class="inline-flex rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">Premium invoice PDF</a>
            <a href="{{ route('reports.premium', ['type' => 'billing-payments', 'q' => $search, 'focus' => $focus]) }}" target="_blank" class="inline-flex rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">Premium payment PDF</a>
        </div>
    </section>

    @if ($billableTenancies->isNotEmpty())
        <section class="space-y-4">
            <div>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Issue Invoices</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Generate rent, service charge, or custom invoices directly from live tenancy records.</p>
            </div>

            <div class="space-y-4">
                @foreach ($billableTenancies as $tenancy)
                    <article class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ $tenancy->property->title }}</h3>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $tenancy->status->badgeClasses() }}">{{ $tenancy->status->label() }}</span>
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $tenancy->unit?->unit_name ?? 'Unit' }} - {{ $tenancy->tenant_name }}</p>
                                <div class="grid gap-3 text-sm text-zinc-700 dark:text-zinc-300 sm:grid-cols-2">
                                    <p>Rent: NGN {{ number_format((float) $tenancy->rent_amount, 2) }}</p>
                                    <p>Service charge: NGN {{ number_format((float) $tenancy->service_charge_amount, 2) }}</p>
                                </div>
                            </div>

                            <form wire:submit="createInvoice({{ $tenancy->id }})" class="w-full max-w-4xl space-y-4 rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50 p-5 dark:border-white/10 dark:bg-zinc-950/40">
                                <div class="grid gap-4 md:grid-cols-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Type</label>
                                        <select wire:model.live="invoiceTypeSelections.{{ $tenancy->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                                            @foreach ($invoiceTypes as $invoiceType)
                                                <option value="{{ $invoiceType->value }}">{{ $invoiceType->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Issue date</label>
                                        <input type="date" wire:model.live="invoiceIssueDates.{{ $tenancy->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Due date</label>
                                        <input type="date" wire:model.live="invoiceDueDates.{{ $tenancy->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Discount</label>
                                        <input type="number" step="0.01" wire:model="invoiceDiscounts.{{ $tenancy->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Line description</label>
                                        <input type="text" wire:model="invoiceDescriptions.{{ $tenancy->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Custom amount</label>
                                        <input type="number" step="0.01" wire:model.live="invoiceAmounts.{{ $tenancy->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Required for caution, inspection, agent, legal, and miscellaneous invoices.</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Notes</label>
                                    <textarea rows="3" wire:model="invoiceNotes.{{ $tenancy->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white"></textarea>
                                </div>

                                <button type="submit" @disabled(! $this->canCreateInvoice($tenancy->id)) wire:loading.attr="disabled" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">Issue invoice</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="space-y-4">
        <div>
            <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ $isTenant ? 'Invoices and Payment Proof' : 'Invoices' }}</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                {{ $isTenant ? 'Each invoice shows the amount due, payment history, and a clear form for submitting payment proof.' : 'Review invoice balances, line items, and payment activity across visible tenancies.' }}
            </p>
        </div>

        <div class="space-y-4">
            @forelse ($invoices as $invoice)
                <article class="overflow-hidden rounded-[1.25rem] border border-zinc-200 bg-white shadow-sm dark:border-white/10 dark:bg-zinc-950/60">
                    <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-white/10 dark:bg-white/5">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">{{ $invoice->invoice_type->label() }} invoice</p>
                                <h3 class="mt-1 break-words text-lg font-semibold text-zinc-950 dark:text-white">{{ $invoice->invoice_number }}</h3>
                                <p class="mt-1 break-words text-sm text-zinc-600 dark:text-zinc-300">{{ $invoice->tenancy->property->title }} - {{ $invoice->tenancy->tenant_name }}</p>
                            </div>

                            <span class="inline-flex w-fit shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $invoice->status->badgeClasses() }}">{{ $invoice->status->label() }}</span>
                        </div>
                    </div>

                    <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_420px]">
                        <div class="min-w-0 space-y-5 p-5">
                            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                    <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Total</p>
                                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">NGN {{ number_format((float) $invoice->total_amount, 2) }}</p>
                                </div>
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                    <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Balance</p>
                                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">NGN {{ number_format((float) $invoice->balance_amount, 2) }}</p>
                                </div>
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                    <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Issued</p>
                                    <p class="mt-2 text-sm font-semibold text-zinc-950 dark:text-white">{{ $invoice->issue_date->format('M j, Y') }}</p>
                                </div>
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                    <p class="text-xs font-medium uppercase text-zinc-500 dark:text-zinc-400">Due</p>
                                    <p class="mt-2 text-sm font-semibold text-zinc-950 dark:text-white">{{ $invoice->due_date->format('M j, Y') }}</p>
                                </div>
                            </div>

                            <section class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                <h4 class="text-sm font-semibold text-zinc-950 dark:text-white">Line items</h4>
                                <div class="mt-3 space-y-2">
                                @foreach ($invoice->items as $item)
                                    <div class="flex flex-col gap-1 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-white/10 dark:bg-white/5 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="break-words text-zinc-700 dark:text-zinc-200">{{ $item->description }}</p>
                                        <p class="shrink-0 font-medium text-zinc-950 dark:text-white">NGN {{ number_format((float) $item->total_amount, 2) }}</p>
                                    </div>
                                @endforeach
                                </div>
                            </section>

                            @if ($invoice->notes)
                                <section class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4 dark:border-cyan-500/20 dark:bg-cyan-500/10">
                                    <h4 class="text-sm font-semibold text-cyan-950 dark:text-cyan-100">Invoice note</h4>
                                    <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-cyan-900 dark:text-cyan-100/90">{{ $invoice->notes }}</p>
                                </section>
                            @endif

                            <section class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-900/70">
                                <h4 class="text-sm font-semibold text-zinc-950 dark:text-white">Payment timeline</h4>
                                <div class="mt-3 space-y-2">
                                    @forelse ($invoice->payments as $payment)
                                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-white/10 dark:bg-white/5">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <p class="break-words font-medium text-zinc-950 dark:text-white">{{ $payment->payment_reference }}</p>
                                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $payment->status->badgeClasses() }}">{{ $payment->status->label() }}</span>
                                            </div>
                                            <p class="mt-2 text-zinc-700 dark:text-zinc-200">NGN {{ number_format((float) $payment->amount, 2) }} via {{ $payment->payment_method?->label() ?? 'payment method not provided' }}</p>
                                            @if ($payment->receipt)
                                                <p class="mt-1 text-xs font-medium text-emerald-700 dark:text-emerald-300">Receipt: {{ $payment->receipt->receipt_number }}</p>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-sm text-zinc-600 dark:text-zinc-300">No payments have been submitted for this invoice yet.</p>
                                    @endforelse
                                </div>
                            </section>

                            @php($billingActivity = $invoice->activityLogs->merge($invoice->payments->flatMap(fn ($payment) => $payment->activityLogs)))
                            <x-activity-timeline :logs="$billingActivity" empty="No billing activity has been recorded yet." />
                        </div>

                        @if ((float) $invoice->balance_amount > 0)
                            <form wire:submit="submitPayment({{ $invoice->id }})" class="border-t border-zinc-200 bg-zinc-50 p-5 dark:border-white/10 dark:bg-white/5 xl:border-l xl:border-t-0">
                                <div class="mb-4">
                                    <h4 class="text-sm font-semibold text-zinc-950 dark:text-white">{{ $isTenant ? 'Submit payment proof' : 'Record payment' }}</h4>
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $isTenant ? 'Upload proof after making a transfer so the team can verify it.' : 'Capture a payment submission against this invoice.' }}</p>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Amount</label>
                                        <input type="number" step="0.01" wire:model.live="paymentAmounts.{{ $invoice->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Method</label>
                                        <select wire:model.live="paymentMethodSelections.{{ $invoice->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                                            @foreach ($paymentMethods as $method)
                                                <option value="{{ $method->value }}">{{ $method->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Paid at</label>
                                    <input type="datetime-local" wire:model="paymentDates.{{ $invoice->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white" />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Payment note</label>
                                    <textarea rows="3" wire:model="paymentNotes.{{ $invoice->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Proof of payment</label>
                                    <input type="file" wire:model="paymentProofs.{{ $invoice->id }}" accept=".pdf,.jpg,.jpeg,.png" class="block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition file:mr-4 file:rounded-full file:border-0 file:bg-zinc-950 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white dark:border-white/10 dark:bg-zinc-950 dark:text-white dark:file:bg-white dark:file:text-zinc-950" />
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Optional. Upload a PDF or image up to 5MB.</p>
                                </div>
                                <button type="submit" @disabled(! $this->canSubmitPayment($invoice->id)) wire:loading.attr="disabled" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">{{ $isTenant ? 'Submit proof' : 'Submit payment' }}</button>
                            </form>
                        @else
                            <div class="border-t border-zinc-200 bg-emerald-50 p-5 text-sm text-emerald-900 dark:border-white/10 dark:bg-emerald-500/10 dark:text-emerald-100 xl:border-l xl:border-t-0">
                                <h4 class="font-semibold">Fully paid</h4>
                                <p class="mt-1">This invoice has no outstanding balance.</p>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-[1.75rem] border border-dashed border-zinc-300 bg-white/70 p-8 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">No invoices yet.</div>
            @endforelse
        </div>
    </section>

    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('landlord'))
        <section class="space-y-4">
            <div>
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Payment Verification Queue</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Review submitted payments, allocate verified funds, and issue receipts automatically.</p>
            </div>

            <div class="space-y-4">
                @forelse ($payments as $payment)
                    <article class="rounded-[1.75rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-cyan-950/5 dark:border-white/10 dark:bg-white/5">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                            <div class="space-y-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ $payment->payment_reference }}</h3>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $payment->status->badgeClasses() }}">{{ $payment->status->label() }}</span>
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $payment->tenancy?->property?->title ?? 'Tenancy payment' }} - {{ $payment->tenantUser?->name ?? 'Unlinked tenant' }}</p>
                                <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                    <div><dt class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Invoice</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $payment->invoice?->invoice_number ?? 'Unallocated' }}</dd></div>
                                    <div><dt class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Amount</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">NGN {{ number_format((float) $payment->amount, 2) }}</dd></div>
                                    <div><dt class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Method</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $payment->payment_method?->label() ?? 'Not provided' }}</dd></div>
                                    <div><dt class="text-xs uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Receipt</dt><dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ $payment->receipt?->receipt_number ?? 'Pending' }}</dd></div>
                                </dl>
                                @if ($payment->notes)
                                    <div class="rounded-2xl border border-zinc-200/80 bg-zinc-50 px-4 py-3 text-sm leading-6 text-zinc-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-200">{{ $payment->notes }}</div>
                                @endif
                            </div>

                            <form wire:submit="reviewPayment({{ $payment->id }})" class="w-full max-w-xl space-y-4 rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50 p-5 dark:border-white/10 dark:bg-zinc-950/40">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Review outcome</label>
                                    <select wire:model.live="reviewStatuses.{{ $payment->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white">
                                        <option value="verified">Verify payment</option>
                                        <option value="rejected">Reject payment</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Review notes</label>
                                    <textarea rows="3" wire:model="reviewNotes.{{ $payment->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white"></textarea>
                                </div>
                                @if (($reviewStatuses[$payment->id] ?? $payment->status->value) === 'rejected')
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Rejection reason</label>
                                        <textarea rows="3" wire:model="rejectionReasons.{{ $payment->id }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 dark:border-white/10 dark:bg-zinc-950 dark:text-white"></textarea>
                                    </div>
                                @endif
                                <button type="submit" wire:loading.attr="disabled" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-zinc-950">Save review</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.75rem] border border-dashed border-zinc-300 bg-white/70 p-8 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300">No payment submissions yet.</div>
                @endforelse
            </div>
        </section>
    @endif
</div>
