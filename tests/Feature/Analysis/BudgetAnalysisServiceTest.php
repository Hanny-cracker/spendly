<?php

use App\Actions\Analysis\BudgetProgressAnalysis;
use App\Data\Report\DateRangeData;
use App\Services\Analysis\BudgetAnalysisService;

// use Mockery;

it('generates budget progress through the analysis service', function () {
    $data = new DateRangeData(
        userId: 1,
        startDate: now()->startOfMonth(),
        endDate: now()->endOfMonth(),
    );

    $expected = [];

    $action = Mockery::mock(BudgetProgressAnalysis::class);

    $action
        ->shouldReceive('handle')
        ->once()
        ->with($data)
        ->andReturn($expected);

    $service = new BudgetAnalysisService($action);

    $result = $service->progress($data);

    expect($result)
        ->toBe($expected);
});
