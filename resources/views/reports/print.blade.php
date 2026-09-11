<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spendly Financial Report</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { margin: 14mm; }
        @media print {
            body { background: #fff !important; }
            .print-controls { display: none !important; }
            [data-report-content] section { break-inside: avoid; }
            [data-report-content] { font-size: 11px; }
        }
    </style>
</head>
<body class="bg-[#f5f2eb] text-stone-900 antialiased">
    <main class="mx-auto max-w-5xl p-4 sm:p-8">
        <div class="print-controls mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-[#fbf8f2] p-4">
            <a href="{{ route('reports') }}" class="text-sm font-semibold text-stone-600">&larr; Reports</a>
            <button type="button" onclick="window.print()" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white">Print Report</button>
        </div>
        <div class="mb-5 flex items-end justify-between border-b border-stone-300 pb-4">
            <div><p class="font-mono text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">Spendly</p><h1 class="mt-1 text-3xl font-semibold">Financial Report</h1></div>
            <p class="text-right text-xs text-stone-500">Generated<br>{{ $generatedAt->format('d M Y H:i') }}</p>
        </div>
        @include('livewire.reports.partials.report-content', ['report' => $report])
    </main>
</body>
</html>
