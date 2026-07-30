<?php

namespace App\Data\Budget;


final readonly class CreateBudgetData
{

    public function __construct(
        public int $userId,
        public int $categoryId,
        public float $amount,
        public int $month,
        public int $year,
    ){}

}