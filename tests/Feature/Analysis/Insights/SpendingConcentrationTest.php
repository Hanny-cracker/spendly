<?php

use App\Actions\Analysis\Insights\SpendingConcentration;
use App\Data\Analysis\CategoryAnalysisData;

it('calculates spending concentration', function () {

    $categories = [

        new CategoryAnalysisData(
            categoryId: 1,
            categoryName: 'Food',
            total: 200000,
            transactionCount: 10,
            percentage: 40,
            averageTransaction: 20000,
        ),

        new CategoryAnalysisData(
            categoryId: 2,
            categoryName: 'Transport',
            total: 150000,
            transactionCount: 8,
            percentage: 30,
            averageTransaction: 18750,
        ),

        new CategoryAnalysisData(
            categoryId: 3,
            categoryName: 'Housing',
            total: 75000,
            transactionCount: 3,
            percentage: 15,
            averageTransaction: 25000,
        ),

        new CategoryAnalysisData(
            categoryId: 4,
            categoryName: 'Entertainment',
            total: 50000,
            transactionCount: 4,
            percentage: 10,
            averageTransaction: 12500,
        ),

        new CategoryAnalysisData(
            categoryId: 5,
            categoryName: 'Other',
            total: 25000,
            transactionCount: 2,
            percentage: 5,
            averageTransaction: 12500,
        ),
    ];

    $result = app(SpendingConcentration::class)
        ->handle($categories);

    expect($result['total'])
        ->toBe(500000.0);

    expect($result['top_category']->categoryName)
        ->toBe('Food');

    expect($result['top_category_percentage'])
        ->toBe(40.0);

    expect($result['top_two_percentage'])
        ->toBe(70.0);

    expect($result['top_three_percentage'])
        ->toBe(85.0);

    expect($result['concentration_level'])
        ->toBe('high');
});

it('detects medium concentration', function () {

    $categories = [

        new CategoryAnalysisData(
            categoryId: 1,
            categoryName: 'Food',
            total: 300000,
            transactionCount: 10,
            percentage: 30,
            averageTransaction: 30000,
        ),

        new CategoryAnalysisData(
            categoryId: 2,
            categoryName: 'Transport',
            total: 200000,
            transactionCount: 8,
            percentage: 20,
            averageTransaction: 25000,
        ),

        new CategoryAnalysisData(
            categoryId: 3,
            categoryName: 'Housing',
            total: 100000,
            transactionCount: 5,
            percentage: 10,
            averageTransaction: 20000,
        ),

        new CategoryAnalysisData(
            categoryId: 4,
            categoryName: 'Shopping',
            total: 400000,
            transactionCount: 10,
            percentage: 40,
            averageTransaction: 40000,
        ),
    ];

    /*
     * Categories should already be sorted by total
     * before being passed to this action.
     */

    $categories = collect($categories)
        ->sortByDesc(fn ($category) => $category->total)
        ->values()
        ->all();

    $result = app(SpendingConcentration::class)
        ->handle($categories);

    expect($result['top_three_percentage'])
        ->toBe(90.0);

    expect($result['concentration_level'])
        ->toBe('high');
});

it('returns low concentration for widely distributed spending', function () {

    $categories = [

        new CategoryAnalysisData(
            categoryId: 1,
            categoryName: 'Food',
            total: 100000,
            transactionCount: 5,
            percentage: 20,
            averageTransaction: 20000,
        ),

        new CategoryAnalysisData(
            categoryId: 2,
            categoryName: 'Transport',
            total: 100000,
            transactionCount: 5,
            percentage: 20,
            averageTransaction: 20000,
        ),

        new CategoryAnalysisData(
            categoryId: 3,
            categoryName: 'Housing',
            total: 100000,
            transactionCount: 5,
            percentage: 20,
            averageTransaction: 20000,
        ),

        new CategoryAnalysisData(
            categoryId: 4,
            categoryName: 'Shopping',
            total: 100000,
            transactionCount: 5,
            percentage: 20,
            averageTransaction: 20000,
        ),

        new CategoryAnalysisData(
            categoryId: 5,
            categoryName: 'Entertainment',
            total: 100000,
            transactionCount: 5,
            percentage: 20,
            averageTransaction: 20000,
        ),
    ];

    $result = app(SpendingConcentration::class)
        ->handle($categories);

    expect($result['top_three_percentage'])
        ->toBe(60.0);

    expect($result['concentration_level'])
        ->toBe('medium');
});

it('handles empty categories', function () {

    $result = app(SpendingConcentration::class)
        ->handle([]);

    expect($result['total'])
        ->toBe(0.0);

    expect($result['top_category'])
        ->toBeNull();

    expect($result['top_three_percentage'])
        ->toBe(0.0);

    expect($result['concentration_level'])
        ->toBe('low');
});