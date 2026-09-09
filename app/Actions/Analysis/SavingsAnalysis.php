<?php

namespace App\Actions\Analysis;

use App\Data\Analysis\SavingsAnalysisData;
use App\Data\Report\DateRangeData;
use App\Enums\AccountType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\GoalContribution;
use App\Models\Transaction;
use App\Models\Transfer;

class SavingsAnalysis
{
    public function handle(DateRangeData $data): SavingsAnalysisData
    {
        $data->validate();

        $currentSavingsBalance = (float) Account::query()
            ->where('user_id', $data->userId)
            ->where('type', AccountType::Savings)
            ->sum('current_balance');

        $externalTransactions = Transaction::query()
            ->where('user_id', $data->userId)
            ->where('status', TransactionStatus::Completed)
            ->whereNull('transfer_id')
            ->whereBetween('date', [$data->startDate, $data->endDate]);

        $income = (float) (clone $externalTransactions)
            ->where('type', TransactionType::Income)
            ->sum('amount');
        $expenses = (float) (clone $externalTransactions)
            ->where('type', TransactionType::Expense)
            ->sum('amount');

        $savingsTransactions = (clone $externalTransactions)
            ->whereHas('account', fn ($query) => $query
                ->where('user_id', $data->userId)
                ->where('type', AccountType::Savings));
        $directSavingsIncome = (float) (clone $savingsTransactions)
            ->where('type', TransactionType::Income)
            ->sum('amount');
        $directSavingsExpenses = (float) (clone $savingsTransactions)
            ->where('type', TransactionType::Expense)
            ->sum('amount');

        $transferQuery = Transfer::query()
            ->where('user_id', $data->userId)
            ->whereBetween('date', [$data->startDate, $data->endDate]);
        $transfersIntoSavings = (float) (clone $transferQuery)
            ->whereHas('toAccount', fn ($query) => $query->where('user_id', $data->userId)->where('type', AccountType::Savings))
            ->whereHas('fromAccount', fn ($query) => $query->where('user_id', $data->userId)->where('type', '!=', AccountType::Savings))
            ->sum('amount');
        $transfersOutOfSavings = (float) (clone $transferQuery)
            ->whereHas('fromAccount', fn ($query) => $query->where('user_id', $data->userId)->where('type', AccountType::Savings))
            ->whereHas('toAccount', fn ($query) => $query->where('user_id', $data->userId)->where('type', '!=', AccountType::Savings))
            ->sum('amount');

        $periodSavingsAccountDeposits = $directSavingsIncome + $transfersIntoSavings;
        $periodSavingsAccountWithdrawals = $directSavingsExpenses + $transfersOutOfSavings;
        $netSavingsAccountActivity = $periodSavingsAccountDeposits - $periodSavingsAccountWithdrawals;
        $goalContributions = GoalContribution::query()
            ->where('user_id', $data->userId)
            ->whereBetween('contributed_at', [$data->startDate, $data->endDate]);
        $periodGoalContributions = (float) (clone $goalContributions)->sum('amount');
        $goalContributionsFromSavingsAccounts = (float) (clone $goalContributions)
            ->whereHas('account', fn ($query) => $query
                ->where('user_id', $data->userId)
                ->where('type', AccountType::Savings))
            ->sum('amount');
        $qualifyingAdditionalGoalSaving = (float) (clone $goalContributions)
            ->where(function ($query) use ($data): void {
                $query->whereNull('account_id')
                    ->orWhereHas('account', fn ($accountQuery) => $accountQuery
                        ->where('user_id', $data->userId)
                        ->where('type', '!=', AccountType::Savings));
            })
            ->sum('amount');

        $qualifyingPeriodSavings = $netSavingsAccountActivity + $qualifyingAdditionalGoalSaving;
        $savingsRate = $income > 0 ? ($qualifyingPeriodSavings / $income) * 100 : 0.0;

        return new SavingsAnalysisData(
            currentSavingsBalance: $currentSavingsBalance,
            periodSavingsAccountDeposits: $periodSavingsAccountDeposits,
            periodSavingsAccountWithdrawals: $periodSavingsAccountWithdrawals,
            netSavingsAccountActivity: $netSavingsAccountActivity,
            periodGoalContributions: $periodGoalContributions,
            goalContributionsFromSavingsAccounts: $goalContributionsFromSavingsAccounts,
            qualifyingAdditionalGoalSaving: $qualifyingAdditionalGoalSaving,
            qualifyingPeriodSavings: $qualifyingPeriodSavings,
            completedIncome: $income,
            expenses: $expenses,
            savingsRate: $savingsRate,
        );
    }
}
