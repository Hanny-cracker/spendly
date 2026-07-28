<?php

namespace App\Services;

use App\Models\Transaction;


class ReportService
{


    public function monthlyIncome($user)
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereMonth('date', now()->month)
            ->sum('amount');
    }



    public function monthlyExpenses($user)
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->sum('amount');
    }



    public function balance($user)
    {
        return $user->accounts()
            ->sum('current_balance');
    }
}
