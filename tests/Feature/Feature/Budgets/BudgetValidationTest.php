<?php

use App\Actions\Budgets\CreateBudget;
use App\Data\Budget\CreateBudgetData;
use App\Enums\BudgetPeriod;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prevents duplicate active budgets for same category', function () {

    $user = User::factory()->create();

    $category = Category::factory()
        ->for($user)
        ->create();

    Budget::factory()
        ->for($user)
        ->for($category)
        ->create([
            'period' => BudgetPeriod::Monthly,
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => Carbon::now()->endOfMonth(),
            'is_active' => true,
        ]);

    $data = new CreateBudgetData(
        userId: $user->id,
        categoryId: $category->id,
        name: 'Food',
        amount: 5000.0,
        period: BudgetPeriod::Monthly,
        startDate: now()->startOfMonth(),
        endDate: now()->endOfMonth(),
        alertPercentage: 80,
        isActive: true,

    );

    expect(

        fn () => app(CreateBudget::class)
            ->handle($data)

    )->toThrow(Exception::class);

});