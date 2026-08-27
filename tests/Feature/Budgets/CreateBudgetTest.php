<?php

use App\Actions\Budgets\CreateBudget;
use App\Data\Budget\CreateBudgetData;
use App\Enums\BudgetPeriod;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a budget', function () {

    $user = User::factory()->create();

    $category = Category::factory()
        ->for($user)
        ->create();

    $data = new CreateBudgetData(
        userId: $user->id,
        categoryId: $category->id,
        name: 'Food Budget',
        amount: 50000.0,
        period: BudgetPeriod::Monthly,
        startDate: now()->startOfMonth(),
        endDate: now()->endOfMonth(),
        alertPercentage: 80,
        isActive: true,
    );

    $budget = app(CreateBudget::class)
        ->handle($data);

    expect($budget)

        ->not->toBeNull()

        ->and($budget->user_id)
        ->toBe($user->id)

        ->and($budget->category_id)
        ->toBe($category->id)

        ->and($budget->amount)
        ->toBe(50000.0);

});