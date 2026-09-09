<?php

namespace App\Data\Analysis;

final readonly class SavingsAnalysisData
{
    public function __construct(
        public float $currentSavingsBalance,
        public float $periodSavingsAccountDeposits,
        public float $periodSavingsAccountWithdrawals,
        public float $netSavingsAccountActivity,
        public float $periodGoalContributions,
        public float $goalContributionsFromSavingsAccounts,
        public float $qualifyingAdditionalGoalSaving,
        public float $qualifyingPeriodSavings,
        public float $completedIncome,
        public float $expenses,
        public float $savingsRate,
    ) {}

    /** @return array<string, float> */
    public function toArray(): array
    {
        return [
            'current_savings_balance' => $this->currentSavingsBalance,
            'period_savings_account_deposits' => $this->periodSavingsAccountDeposits,
            'period_savings_account_withdrawals' => $this->periodSavingsAccountWithdrawals,
            'net_savings_account_activity' => $this->netSavingsAccountActivity,
            'period_savings_account_activity' => $this->netSavingsAccountActivity,
            'period_goal_contributions' => $this->periodGoalContributions,
            'goal_contributions_from_savings_accounts' => $this->goalContributionsFromSavingsAccounts,
            'qualifying_additional_goal_saving' => $this->qualifyingAdditionalGoalSaving,
            'qualifying_goal_saving' => $this->qualifyingAdditionalGoalSaving,
            'qualifying_period_savings' => $this->qualifyingPeriodSavings,
            'total_period_saving' => $this->qualifyingPeriodSavings,
            'savings' => $this->qualifyingPeriodSavings,
            'completed_income' => $this->completedIncome,
            'income' => $this->completedIncome,
            'expenses' => $this->expenses,
            'savings_rate' => $this->savingsRate,
            'rate' => $this->savingsRate,
        ];
    }
}
