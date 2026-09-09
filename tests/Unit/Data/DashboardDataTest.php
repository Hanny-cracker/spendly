<?php

use App\Data\Analysis\BudgetProgressData;
use App\Data\Dashboard\AccountSummaryData;
use App\Data\Dashboard\DashboardData;
use App\Data\Dashboard\TransactionSummaryData;
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

    $account = new AccountSummaryData(
        id: 1,
        publicId: 'acc_example',
        name: 'Main Wallet',
        type: 'cash',
        currency: 'FCFA',
        currentBalance: 125000,
        color: '#047857',
        isDefault: true,
    );

    $transaction = new TransactionSummaryData(
        id: 5,
        publicId: 'txn_example',
        title: 'Market shopping',
        categoryName: 'Food',
        categoryIcon: 'wallet',
        categoryColor: '#dc2626',
        accountName: 'Main Wallet',
        date: '2026-01-15',
        amount: 12500,
        type: 'expense',
        status: 'completed',
        isTransfer: false,
    );

    $dashboard = new DashboardData(
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
        totalBalance: 125000,

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

        accounts: [$account],
        recentTransactions: [$transaction],
    );

    $result = $dashboard->toArray();

    expect($result)
        ->toHaveKeys([
            'start_date',
            'end_date',
            'total_balance',
            'savings_balance',
            'goal_savings',
            'reports',
            'insights',
            'budgets',
            'accounts',
            'recent_transactions',
        ]);

    expect($result['total_balance'])
        ->toBe(125000.0)
        ->and($result['accounts'])
        ->toBe([[
            'id' => 1,
            'public_id' => 'acc_example',
            'name' => 'Main Wallet',
            'type' => 'cash',
            'currency' => 'FCFA',
            'current_balance' => 125000.0,
            'color' => '#047857',
            'is_default' => true,
        ]]);

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

    expect($result['recent_transactions'])
        ->toBe([[
            'id' => 5,
            'public_id' => 'txn_example',
            'title' => 'Market shopping',
            'category_name' => 'Food',
            'category_icon' => 'wallet',
            'category_color' => '#dc2626',
            'account_name' => 'Main Wallet',
            'date' => '2026-01-15',
            'amount' => 12500.0,
            'type' => 'expense',
            'status' => 'completed',
            'is_transfer' => false,
        ]]);
});
