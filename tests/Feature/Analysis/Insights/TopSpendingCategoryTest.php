<?php

use App\Actions\Analysis\Insights\TopSpendingCategory;
use App\Data\Analysis\CategoryAnalysisData;

it('returns the highest spending category', function () {

    $categories = [
        new CategoryAnalysisData(
            categoryId: 1,
            categoryName: 'Food',
            total: 150000,
            transactionCount: 10,
            percentage: 50,
            averageTransaction: 15000,
        ),

        new CategoryAnalysisData(
            categoryId: 2,
            categoryName: 'Transport',
            total: 80000,
            transactionCount: 8,
            percentage: 26.67,
            averageTransaction: 10000,
        ),

        new CategoryAnalysisData(
            categoryId: 3,
            categoryName: 'Entertainment',
            total: 70000,
            transactionCount: 5,
            percentage: 23.33,
            averageTransaction: 14000,
        ),
    ];

    $result = app(TopSpendingCategory::class)
        ->handle($categories);

    expect($result)
        ->toBeInstanceOf(CategoryAnalysisData::class)
        ->and($result->categoryName)
        ->toBe('Food')
        ->and($result->total)
        ->toBe(150000.0);
});

it('returns null when there are no categories', function () {

    $result = app(TopSpendingCategory::class)
        ->handle([]);

    expect($result)
        ->toBeNull();
});