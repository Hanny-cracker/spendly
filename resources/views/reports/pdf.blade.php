<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spendly Financial Report</title>
    <style>
        @page { margin: 34px 42px 44px; }
        body { color: #292524; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.45; }
        h1, h2, h3, p { margin: 0; }
        .brand { color: #047857; font-size: 12px; font-weight: bold; letter-spacing: 2px; }
        h1 { font-size: 24px; margin-top: 5px; }
        .muted { color: #78716c; }
        .header { border-bottom: 1px solid #d6d3d1; padding-bottom: 16px; }
        .generated { float: right; text-align: right; }
        .section { margin-top: 20px; page-break-inside: avoid; }
        .section-title { border-bottom: 1px solid #d6d3d1; color: #57534e; font-size: 9px; letter-spacing: 1.2px; padding-bottom: 5px; text-transform: uppercase; }
        table { border-collapse: collapse; margin-top: 8px; width: 100%; }
        th, td { border-bottom: 1px solid #e7e5e4; padding: 6px 5px; text-align: left; }
        th { color: #78716c; font-size: 8px; text-transform: uppercase; }
        .number { font-family: DejaVu Sans Mono, monospace; text-align: right; }
        .positive { color: #047857; }
        .negative { color: #b91c1c; }
        .summary td { border: 1px solid #e7e5e4; width: 25%; }
        .summary strong { display: block; font-size: 15px; margin-top: 4px; }
        .empty { border: 1px dashed #a8a29e; margin-top: 18px; padding: 18px; text-align: center; }
        .footer { bottom: -26px; color: #a8a29e; font-size: 8px; position: fixed; text-align: center; width: 100%; }
    </style>
</head>
<body>
@php($cashFlow = $report['cash_flow'] ?? [])
@php($summary = $report['transaction_summary'] ?? [])
<div class="footer">Spendly &middot; Financial Report</div>
<header class="header">
    <div class="generated"><span class="muted">Generated</span><br>{{ now()->format('d M Y') }}</div>
    <p class="brand">SPENDLY</p>
    <h1>Financial Report</h1>
    <p class="muted">{{ \Carbon\Carbon::parse($report['start_date'])->format('d F Y') }} &ndash; {{ \Carbon\Carbon::parse($report['end_date'])->format('d F Y') }}</p>
</header>

@if (($summary['total_transactions'] ?? 0) === 0)
    <div class="empty"><strong>No financial activity was recorded for this period.</strong><br><span class="muted">The report remains available with zero balances.</span></div>
@endif

<section class="section">
    <h2 class="section-title">Financial Summary</h2>
    <table class="summary"><tr>
        <td>Income<strong class="positive">{{ number_format($cashFlow['total_income'] ?? 0, 0) }} FCFA</strong></td>
        <td>Expenses<strong class="negative">{{ number_format($cashFlow['total_expenses'] ?? 0, 0) }} FCFA</strong></td>
        <td>Net Cash Flow<strong>{{ number_format($cashFlow['net_cash_flow'] ?? 0, 0) }} FCFA</strong></td>
        <td>Savings Rate<strong>{{ number_format($cashFlow['savings_rate'] ?? 0, 1) }}%</strong></td>
    </tr></table>
</section>

<section class="section">
    <h2 class="section-title">Transaction Summary</h2>
    <table><tbody>
        @foreach (['Total Transactions' => $summary['total_transactions'] ?? 0, 'Income Transactions' => $summary['income_transactions'] ?? 0, 'Expense Transactions' => $summary['expense_transactions'] ?? 0, 'Largest Income' => number_format($summary['largest_income'] ?? 0, 0).' FCFA', 'Largest Expense' => number_format($summary['largest_expense'] ?? 0, 0).' FCFA'] as $label => $value)
            <tr><td>{{ $label }}</td><td class="number"><strong>{{ $value }}</strong></td></tr>
        @endforeach
    </tbody></table>
</section>

@foreach ([['Expenses by Category', $report['expense_categories'] ?? []], ['Income by Category', $report['income_categories'] ?? []]] as [$heading, $categories])
    <section class="section">
        <h2 class="section-title">{{ $heading }}</h2>
        <table><thead><tr><th>Category</th><th class="number">Transactions</th><th class="number">Share</th><th class="number">Amount</th></tr></thead><tbody>
        @forelse ($categories as $category)
            <tr><td>{{ $category['category_name'] }}</td><td class="number">{{ $category['transaction_count'] }}</td><td class="number">{{ number_format($category['percentage'], 1) }}%</td><td class="number">{{ number_format($category['total'], 0) }} FCFA</td></tr>
        @empty
            <tr><td colspan="4" class="muted">No activity in this category group.</td></tr>
        @endforelse
        </tbody></table>
    </section>
@endforeach

<section class="section">
    <h2 class="section-title">Account Activity</h2>
    <p class="muted">Selected-period activity; these values are not historical closing balances.</p>
    <table><thead><tr><th>Account</th><th class="number">Income</th><th class="number">Expenses</th><th class="number">Net Activity</th><th class="number">Transactions</th></tr></thead><tbody>
    @foreach ($report['account_activity'] ?? [] as $account)
        <tr><td>{{ $account['name'] }}</td><td class="number">{{ number_format($account['income'], 0) }} FCFA</td><td class="number">{{ number_format($account['expenses'], 0) }} FCFA</td><td class="number">{{ number_format($account['net_activity'], 0) }} FCFA</td><td class="number">{{ $account['transaction_count'] }}</td></tr>
    @endforeach
    </tbody></table>
</section>

@if (($summary['total_transactions'] ?? 0) > 0)
    <section class="section">
        <h2 class="section-title">Report Insights</h2>
        <table><tbody>
            <tr><td>Largest spending category</td><td class="number"><strong>{{ data_get($report, 'expense_categories.0.category_name', 'None') }} &mdash; {{ number_format(data_get($report, 'expense_categories.0.total', 0), 0) }} FCFA</strong></td></tr>
            <tr><td>Savings rate</td><td class="number"><strong>{{ number_format($cashFlow['savings_rate'] ?? 0, 1) }}%</strong></td></tr>
            <tr><td>Financial health</td><td class="number"><strong>{{ ucfirst(data_get($report, 'insights.financial_health.status', 'neutral')) }}</strong></td></tr>
        </tbody></table>
    </section>
@endif
</body>
</html>
