<?php

namespace App\Services\Reports;

use App\Actions\Analysis\CashFlowAnalysis;
use App\Data\Report\DateRangeData;
use App\Data\Report\FinancialReportData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\Analysis\InsightsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FinancialReportService
{
    public function __construct(private CashFlowAnalysis $cashFlowAnalysis, private InsightsService $insightsService) {}

    public function summary(DateRangeData $data): FinancialReportData
    {
        $data->validate();
        $transactions = $this->transactions($data)->get();
        $income = $transactions->where('type', TransactionType::Income);
        $expenses = $transactions->where('type', TransactionType::Expense);

        return new FinancialReportData(
            startDate: $data->startDate, endDate: $data->endDate, cashFlow: $this->cashFlowAnalysis->handle($data),
            expenseCategories: $this->categoryBreakdown($expenses), incomeCategories: $this->categoryBreakdown($income),
            accountActivity: $this->accountActivity($data->userId, $transactions), transactionSummary: $this->transactionSummary($income, $expenses),
            insights: $this->insightsService->summary($data),
        );
    }

    /** @return Builder<Transaction> */
    public function transactions(DateRangeData $data): Builder
    {
        $data->validate();

        return Transaction::query()
            ->where('user_id', $data->userId)
            ->where('status', TransactionStatus::Completed)
            ->whereNull('transfer_id')
            ->whereBetween('date', [$data->startDate, $data->endDate])
            ->with(['account:id,name,currency', 'category:id,name'])
            ->orderBy('date')
            ->orderBy('id');
    }

    private function categoryBreakdown(Collection $transactions): array
    {
        $total = (float) $transactions->sum('amount');

        return $transactions->filter(fn (Transaction $transaction): bool => $transaction->category !== null)->groupBy('category_id')->map(function (Collection $items) use ($total): array {
            $amount = (float) $items->sum('amount');

            return ['category_id' => $items->first()->category_id, 'category_name' => $items->first()->category->name, 'total' => $amount, 'transaction_count' => $items->count(), 'percentage' => $total > 0 ? ($amount / $total) * 100 : 0.0];
        })->sortByDesc('total')->values()->all();
    }

    private function accountActivity(int $userId, Collection $transactions): array
    {
        return Account::query()->where('user_id', $userId)->orderByDesc('is_default')->orderBy('name')->get(['id', 'public_id', 'name'])->map(function (Account $account) use ($transactions): array {
            $items = $transactions->where('account_id', $account->id);
            $income = (float) $items->where('type', TransactionType::Income)->sum('amount');
            $expenses = (float) $items->where('type', TransactionType::Expense)->sum('amount');

            return ['id' => $account->id, 'public_id' => $account->public_id, 'name' => $account->name, 'income' => $income, 'expenses' => $expenses, 'net_activity' => $income - $expenses, 'transaction_count' => $items->count()];
        })->all();
    }

    private function transactionSummary(Collection $income, Collection $expenses): array
    {
        return ['total_transactions' => $income->count() + $expenses->count(), 'income_transactions' => $income->count(), 'expense_transactions' => $expenses->count(), 'largest_income' => (float) $income->max('amount'), 'largest_expense' => (float) $expenses->max('amount'), 'average_income' => (float) ($income->avg('amount') ?? 0), 'average_expense' => (float) ($expenses->avg('amount') ?? 0), 'largest_income_items' => $this->largest($income), 'largest_expense_items' => $this->largest($expenses)];
    }

    private function largest(Collection $transactions): array
    {
        return $transactions->sortByDesc('amount')->take(5)->map(fn (Transaction $transaction): array => ['public_id' => $transaction->public_id, 'title' => $transaction->title, 'amount' => (float) $transaction->amount])->values()->all();
    }
}
