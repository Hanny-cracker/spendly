<?php

namespace App\Actions\Analysis\Insights;

use App\Actions\Analysis\SavingsAnalysis;
use App\Data\Report\DateRangeData;

class SavingsRate
{
    public function __construct(private SavingsAnalysis $savingsAnalysis) {}

    public function handle(DateRangeData $data): array
    {
        return $this->savingsAnalysis->handle($data)->toArray();
    }
}
