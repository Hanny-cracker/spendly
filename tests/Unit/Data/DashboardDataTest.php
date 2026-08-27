<?php

use App\Data\Analysis\BudgetProgressData;
use App\Data\Dashboard\DashboardData;
use Carbon\Carbon;

it('converts dashboard data to an array', function () {

    $budget = new BudgetProgressData(
        budgetId: 1,
        budgetName: 'Food Budget',
        budgetAmount: 100000,
        spentAmount: 70000,
        remainingAmount: 30000,
        percentageUsed: 70,
        status: 'warning',
    );

    $dashboard = new DashboardData(
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),

        reports: [
            'expense' => [],
            'income' => [],
            'cash_flow' => [],
            'categories' => [],
        ],

        insights: [
            'spending' => [],
            'income' => [],
            'cash_flow' => [],
            'categories' => [],
            'savings_rate' => [],
            'spending_trend' => [],
            'spending_concentration' => [],
            'financial_health' => [],
        ],

        budgets: [
            $budget,
        ],

        recentTransactions: [],
    );

    $result = $dashboard->toArray();

    expect($result)
        ->toHaveKeys([
            'start_date',
            'end_date',
            'reports',
            'insights',
            'budgets',
            'recent_transactions',
        ]);

    expect($result['budgets'])
        ->toHaveCount(1);

    expect($result['budgets'][0])
        ->toBe([
            'budget_id' => 1,
            'budget_name' => 'Food Budget',
            'budget_amount' => 100000.0,
            'spent_amount' => 70000.0,
            'remaining_amount' => 30000.0,
            'percentage_used' => 70.0,
            'status' => 'warning',
        ]);
});