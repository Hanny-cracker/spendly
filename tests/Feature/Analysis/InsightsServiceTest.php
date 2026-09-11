<?php

use App\Data\Report\DateRangeData;
use App\Models\User;
use App\Services\Analysis\InsightsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates complete financial insights', function () {

    $user = User::factory()->create();

    $data = new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );

    $result = app(InsightsService::class)
        ->summary($data);

    expect($result)
        ->toHaveKeys([
            'spending',
            'income',
            'cash_flow',
            'categories',
            'savings_rate',
            'spending_trend',
            'spending_concentration',
            'financial_health',
        ]);
});
