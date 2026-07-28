<?php

namespace App\Services;


use App\Models\Budget;


class BudgetService
{


    public function spent(Budget $budget)
    {

        return $budget
            ->category
            ->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $budget->month)
            ->whereYear('date', $budget->year)
            ->sum('amount');
    }



    public function remaining(Budget $budget)
    {

        return $budget->amount - $this->spent($budget);
    }



    public function percentage(Budget $budget)
    {

        if ($budget->amount == 0) {
            return 0;
        }


        return ($this->spent($budget) / $budget->amount) * 100;
    }
}
