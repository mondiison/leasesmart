<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }}</title>
    <style>
        @page { margin: 28px; }
        body { font-family: DejaVu Sans, sans-serif; color: #18181b; font-size: 12px; line-height: 1.45; }
        .cover { background: #0f172a; color: #fff; padding: 28px; border-radius: 14px; margin-bottom: 18px; }
        .eyebrow { color: #a5f3fc; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; font-weight: bold; }
        h1 { margin: 10px 0 0; font-size: 26px; }
        h2 { margin: 0 0 10px; font-size: 15px; color: #18181b; }
        .muted { color: #71717a; }
        .grid { width: 100%; border-collapse: separate; border-spacing: 8px; margin: 0 -8px 18px; }
        .card { border: 1px solid #e4e4e7; border-radius: 12px; padding: 12px; background: #fff; vertical-align: top; }
        .metric { font-size: 20px; font-weight: bold; color: #09090b; margin-top: 6px; }
        .label { font-size: 10px; text-transform: uppercase; color: #71717a; font-weight: bold; }
        .chart { border: 1px solid #e4e4e7; border-radius: 12px; padding: 14px; background: #f8fafc; margin-bottom: 14px; }
        .bar-wrap { height: 10px; background: #e4e4e7; border-radius: 999px; overflow: hidden; }
        .bar { height: 10px; background: #0891b2; border-radius: 999px; }
        .row-label { width: 72%; display: inline-block; font-weight: bold; color: #3f3f46; }
        .row-value { width: 25%; display: inline-block; text-align: right; color: #71717a; }
        table.detail { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.detail td { border-top: 1px solid #e4e4e7; padding: 8px; vertical-align: top; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; background: #ecfeff; color: #155e75; font-size: 10px; font-weight: bold; }
        .section { margin-bottom: 18px; }
    </style>
</head>
<body>
    <div class="cover">
        <div class="eyebrow">LeaseSmart Premium Report</div>
        <h1>{{ $report['title'] }}</h1>
        <p>{{ $report['subtitle'] }}</p>
        <p class="muted">Generated {{ $report['generatedAt']->format('M j, Y g:i A') }}</p>
    </div>

    <table class="grid">
        <tr>
            @foreach ($report['filters'] as $label => $value)
                <td class="card">
                    <div class="label">{{ $label }}</div>
                    <div>{{ $value === '' ? 'All' : $value }}</div>
                </td>
            @endforeach
        </tr>
    </table>

    <table class="grid">
        <tr>
            @foreach ($report['metrics'] as $metric)
                <td class="card">
                    <div class="label">{{ $metric['label'] }}</div>
                    <div class="metric">{{ $metric['value'] }}</div>
                </td>
            @endforeach
        </tr>
    </table>

    @foreach ($report['charts'] as $chart)
        @php($max = max(1, collect($chart['items'])->max('value') ?? 1))
        <div class="chart">
            <h2>{{ $chart['title'] }}</h2>
            @forelse ($chart['items'] as $item)
                <div style="margin-bottom: 9px;">
                    <span class="row-label">{{ $item['label'] }}</span>
                    <span class="row-value">{{ number_format($item['value']) }}</span>
                    <div class="bar-wrap">
                        <div class="bar" style="width: {{ max(8, ($item['value'] / $max) * 100) }}%;"></div>
                    </div>
                </div>
            @empty
                <p class="muted">No chart data available.</p>
            @endforelse
        </div>
    @endforeach

    <div class="section">
        <h2>Report Detail</h2>
        <table class="detail">
            <tbody>
            @forelse ($report['rows'] as $row)
                <tr>
                    @foreach ($row as $index => $cell)
                        <td>
                            @if ($loop->last)
                                <span class="badge">{{ $cell }}</span>
                            @else
                                {{ $cell }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr><td>No records matched this report.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
