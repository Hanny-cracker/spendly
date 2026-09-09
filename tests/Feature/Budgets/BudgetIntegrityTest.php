<?php

use App\Actions\Budgets\CreateBudget;
use App\Actions\Budgets\ToggleBudgetStatus;
use App\Actions\Budgets\UpdateBudget;
use App\Data\Budget\CreateBudgetData;
use App\Data\Budget\UpdateBudgetData;
use App\Enums\BudgetPeriod;
use App\Enums\CategoryType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

function budgetData(User $user, Category $category, string $start, string $end, bool $active = true, BudgetPeriod $period = BudgetPeriod::Monthly): CreateBudgetData
{
    return new CreateBudgetData($user->id, $category->id, 'Category Budget', 100000, $period, Carbon::parse($start), Carbon::parse($end), 80, $active);
}

it('blocks overlapping active category budgets regardless of period label', function () {
    $user = User::factory()->create();
    $food = Category::factory()->for($user)->create(['name' => 'Food', 'type' => CategoryType::Expense]);
    app(CreateBudget::class)->handle(budgetData($user, $food, '2026-09-01', '2026-09-30'));

    expect(fn () => app(CreateBudget::class)->handle(budgetData($user, $food, '2026-09-15', '2026-10-15', period: BudgetPeriod::Custom)))
        ->toThrow(ValidationException::class, 'A budget already exists for Food during this period.');
});

it('allows non-overlapping periods different categories and different users', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $food = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $transport = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $otherFood = Category::factory()->for($other)->create(['type' => CategoryType::Expense]);
    $action = app(CreateBudget::class);

    $action->handle(budgetData($user, $food, '2026-09-01', '2026-09-30'));
    $action->handle(budgetData($user, $food, '2026-10-01', '2026-10-31'));
    $action->handle(budgetData($user, $transport, '2026-09-01', '2026-09-30'));
    $action->handle(budgetData($other, $otherFood, '2026-09-01', '2026-09-30'));

    expect(Budget::query()->count())->toBe(4);
});

it('excludes the edited budget itself and blocks conflicting activation', function () {
    $user = User::factory()->create();
    $food = Category::factory()->for($user)->create(['name' => 'Food', 'type' => CategoryType::Expense]);
    $active = app(CreateBudget::class)->handle(budgetData($user, $food, '2026-09-01', '2026-09-30'));
    $update = new UpdateBudgetData($active->name, 120000, 80, true, $food->id, $active->period, $active->start_date, $active->end_date);

    expect(app(UpdateBudget::class)->handle($active, $update)->amount)->toBe(120000.0);

    $inactive = app(CreateBudget::class)->handle(budgetData($user, $food, '2026-09-15', '2026-09-25', active: false));
    expect(fn () => app(ToggleBudgetStatus::class)->handle($inactive))->toThrow(ValidationException::class);
    expect($inactive->refresh()->is_active)->toBeFalse();
});
