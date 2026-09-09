<?php

namespace App\Services\Dashboard;

use App\Actions\Goals\CalculateGoalProgress;
use App\Data\Dashboard\AccountSummaryData;
use App\Data\Dashboard\DashboardData;
use App\Data\Dashboard\TransactionSummaryData;
use App\Data\Report\DateRangeData;
use App\Models\Account;
use App\Models\Goal;
use App\Models\Transaction;
use App\Services\Analysis\BudgetAnalysisService;
use App\Services\Analysis\InsightsService;
use App\Services\Reports\ReportService;

class DashboardService
{
    public function __construct(
        protected ReportService $reportService,
        protected InsightsService $insightsService,
        protected BudgetAnalysisService $budgetAnalysisService,
        protected CalculateGoalProgress $calculateGoalProgress,
    ) {}

    /**
     * Generate the complete dashboard data.
     */
    public function summary(
        DateRangeData $data
    ): DashboardData {

        $data->validate();

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        $reports = $this->reportService->summary($data);

        /*
        |--------------------------------------------------------------------------
        | Analysis & Insights
        |--------------------------------------------------------------------------
        */

        $insights = $this->insightsService->summary($data);

        /*
        |--------------------------------------------------------------------------
        | Budget Progress
        |--------------------------------------------------------------------------
        */

        $budgets = $this->budgetAnalysisService->progress($data);

        /*
        |--------------------------------------------------------------------------
        | Recent Transactions
        |--------------------------------------------------------------------------
        |
        | Recent transactions are intentionally independent
        | of the selected report period.
        |
        */

        $recentTransactions = Transaction::query()
            ->where('user_id', $data->userId)
            ->with([
                'category' => fn ($query) => $query->where('user_id', $data->userId),
                'account' => fn ($query) => $query->where('user_id', $data->userId),
            ])
            ->latest('date')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(
                fn (Transaction $transaction) => new TransactionSummaryData(
                    id: $transaction->id,
                    publicId: $transaction->public_id,
                    title: $transaction->title,
                    categoryName: $transaction->category?->name,
                    categoryIcon: $transaction->category?->icon,
                    categoryColor: $transaction->category?->color,
                    accountName: $transaction->account?->name ?? 'Unknown account',
                    date: $transaction->date->toDateString(),
                    amount: (float) $transaction->amount,
                    type: $transaction->type->value,
                    status: $transaction->status->value,
                    isTransfer: $transaction->transfer_id !== null,
                )
            )
            ->all();

        $accounts = Account::query()
            ->where('user_id', $data->userId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $totalBalance = (float) $accounts->sum('current_balance');

        $accountSummaries = $accounts
            ->map(
                fn (Account $account) => new AccountSummaryData(
                    id: $account->id,
                    publicId: $account->public_id,
                    name: $account->name,
                    type: $account->type->value,
                    currency: $account->currency,
                    currentBalance: (float) $account->current_balance,
                    color: $account->color,
                    isDefault: (bool) $account->is_default,
                )
            )
            ->all();

        $goalProgress = Goal::query()
            ->where('user_id', $data->userId)
            ->orderBy('target_date')
            ->orderBy('name')
            ->get()
            ->map(fn (Goal $goal): array => $this->calculateGoalProgress->handle($goal)->toArray())
            ->all();

        return new DashboardData(
            startDate: $data->startDate,
            endDate: $data->endDate,

            totalBalance: $totalBalance,
            accounts: $accountSummaries,

            reports: $reports,
            insights: $insights,
            budgets: $budgets,
            recentTransactions: $recentTransactions,
            savingsBalance: (float) ($insights['savings_rate']['current_savings_balance'] ?? 0),
            goalSavings: [
                'total' => (float) collect($goalProgress)->sum('current_amount'),
                'goals' => $goalProgress,
            ],
        );
    }
}
