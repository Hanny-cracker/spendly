<?php

use App\Data\Report\DateRangeData;
use App\Services\Analysis\AnalysisService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function analysisServiceDateRange(User $user): DateRangeData
{
    return new DateRangeData(
        userId: $user->id,
        startDate: Carbon::parse('2026-01-01'),
        endDate: Carbon::parse('2026-01-31'),
    );
}

it('generates spending analysis', function () {

    $user = User::factory()->create();

    $result = app(AnalysisService::class)
        ->spending(
            analysisServiceDateRange($user)
        );

    expect($result)
        ->toBeInstanceOf(
            \App\Data\Analysis\SpendingAnalysisData::class
        );
});

it('generates income analysis', function () {

    $user = User::factory()->create();

    $result = app(AnalysisService::class)
        ->income(
            analysisServiceDateRange($user)
        );

    expect($result)
        ->toBeInstanceOf(
            \App\Data\Analysis\IncomeAnalysisData::class
        );
});

it('generates cash flow analysis', function () {

    $user = User::factory()->create();

    $result = app(AnalysisService::class)
        ->cashFlow(
            analysisServiceDateRange($user)
        );

    expect($result)
        ->toBeInstanceOf(
            \App\Data\Analysis\CashFlowAnalysisData::class
        );
});

it('generates category analysis', function () {

    $user = User::factory()->create();

    $result = app(AnalysisService::class)
        ->categories(
            analysisServiceDateRange($user)
        );

    expect($result)
        ->toBeArray();
});

it('generates a complete analysis summary', function () {

    $user = User::factory()->create();

    $result = app(AnalysisService::class)
        ->summary(
            analysisServiceDateRange($user)
        );

    expect($result)
        ->toHaveKeys([
            'spending',
            'income',
            'cash_flow',
            'categories',
        ]);

    expect($result['spending'])
        ->toBeInstanceOf(
            \App\Data\Analysis\SpendingAnalysisData::class
        );

    expect($result['income'])
        ->toBeInstanceOf(
            \App\Data\Analysis\IncomeAnalysisData::class
        );

    expect($result['cash_flow'])
        ->toBeInstanceOf(
            \App\Data\Analysis\CashFlowAnalysisData::class
        );

    expect($result['categories'])
        ->toBeArray();
});