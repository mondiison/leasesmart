<x-layouts.app>
    <style>
        @media print {
            body { background: #fff !important; }
            aside, header, .no-print { display: none !important; }
            main, .print-report { padding: 0 !important; margin: 0 !important; max-width: none !important; }
            .report-page { box-shadow: none !important; border: 0 !important; }
        }
    </style>

    <div class="print-report space-y-5">
        <div class="no-print flex justify-end">
            <button type="button" onclick="window.print()" class="w-full rounded-full bg-zinc-950 px-5 py-3 text-sm font-medium text-white sm:w-auto dark:bg-white dark:text-zinc-950">Save as PDF</button>
        </div>

        <section class="report-page overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-xl shadow-cyan-950/5 sm:rounded-[1.5rem] dark:border-white/10 dark:bg-zinc-950">
            <div class="bg-zinc-950 p-5 text-white sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-200 sm:text-sm sm:tracking-[0.28em]">LeaseSmart Premium Report</p>
                <h1 class="mt-3 break-words text-2xl font-semibold sm:text-3xl">{{ $report['title'] }}</h1>
                <p class="mt-2 text-sm text-zinc-300">{{ $report['subtitle'] }}</p>
                <p class="mt-6 text-xs text-zinc-400">Generated {{ $report['generatedAt']->format('M j, Y g:i A') }}</p>
            </div>

            <div class="space-y-6 p-4 sm:space-y-8 sm:p-8">
                <section class="grid gap-3 sm:grid-cols-3">
                    @foreach ($report['filters'] as $label => $value)
                        <div class="min-w-0 rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                            <p class="text-xs font-semibold uppercase text-zinc-500">{{ $label }}</p>
                            <p class="mt-1 break-words text-sm font-medium text-zinc-950">{{ $value === '' ? 'All' : $value }}</p>
                        </div>
                    @endforeach
                </section>

                <section class="grid gap-4 md:grid-cols-4">
                    @foreach ($report['metrics'] as $metric)
                        <div class="min-w-0 rounded-2xl border border-zinc-200 bg-white p-4 sm:p-5">
                            <p class="text-sm text-zinc-500">{{ $metric['label'] }}</p>
                            <p class="mt-2 break-words text-2xl font-semibold text-zinc-950">{{ $metric['value'] }}</p>
                        </div>
                    @endforeach
                </section>

                <section class="grid gap-5 xl:grid-cols-2">
                    @foreach ($report['charts'] as $chart)
                        @php($max = max(1, collect($chart['items'])->max('value') ?? 1))
                        <div class="min-w-0 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 sm:p-5">
                            <h2 class="text-lg font-semibold text-zinc-950">{{ $chart['title'] }}</h2>
                            <div class="mt-5 space-y-3">
                                @forelse ($chart['items'] as $item)
                                    <div>
                                        <div class="mb-1 flex flex-col gap-1 text-sm sm:flex-row sm:justify-between sm:gap-3">
                                            <span class="break-words font-medium text-zinc-700">{{ $item['label'] }}</span>
                                            <span class="text-zinc-500">{{ number_format($item['value']) }}</span>
                                        </div>
                                        <div class="h-3 overflow-hidden rounded-full bg-zinc-200">
                                            <div class="h-full rounded-full bg-cyan-600" style="width: {{ max(8, ($item['value'] / $max) * 100) }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-zinc-500">No chart data available.</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </section>

                <section>
                    <h2 class="text-lg font-semibold text-zinc-950">Report Detail</h2>
                    <div class="mt-4 overflow-x-auto rounded-2xl border border-zinc-200">
                        <table class="min-w-full text-left text-sm">
                            <tbody class="divide-y divide-zinc-200">
                                @forelse ($report['rows'] as $row)
                                    <tr>
                                        @foreach ($row as $cell)
                                            <td class="min-w-40 p-3 align-top text-zinc-700">{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr><td class="p-4 text-zinc-500">No records matched this report.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </section>
    </div>
</x-layouts.app>
