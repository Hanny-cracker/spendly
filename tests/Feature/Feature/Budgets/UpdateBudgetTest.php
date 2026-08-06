<?php

use App\Actions\Budgets\UpdateBudget;
use App\Data\Budget\UpdateBudgetData;
use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('updates a budget', function () {

    $budget = Budget::factory()->create();

    $data = new UpdateBudgetData(
        name: 'Updated Budget',
        amount: 80000,
        alertPercentage: 90,
        isActive: false,
    );

    $updated = app(UpdateBudget::class)
        ->handle($budget, $data);

expect($updated->fresh())
    ->name->toBe('Updated Budget')
    ->and($updated->fresh()->amount)->toBe(80000.0)
    ->and($updated->fresh()->alert_percentage)->toBe(90)
    ->and($updated->fresh()->is_active)->toBeFalse();
});