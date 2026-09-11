<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;

class ReportService
{
    public function monthlyIncome($user)
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', TransactionType::Income)
            ->whereNull('transfer_id')
            ->whereMonth('date', now()->month)
            ->sum('amount');
    }

    public function monthlyExpenses($user)
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', TransactionType::Expense)
            ->whereNull('transfer_id')
            ->whereMonth('date', now()->month)
            ->sum('amount');
    }

    public function balance($user)
    {
        return $user->accounts()
            ->sum('current_balance');
    }
}
