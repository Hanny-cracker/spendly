<?php

use App\Enums\CategoryType;
use App\Livewire\Budgets\Create;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Livewire\Livewire;

it('protects the create page from guests', function () {
    $this->get(route('budgets.create'))->assertRedirect(route('login'));
});

it('creates an owned budget for an expense category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $this->actingAs($user);

    Livewire::test(Create::class)->set('name', 'Monthly Food')->set('categoryId', (string) $category->id)->set('amount', '50000')->set('period', 'monthly')->set('startDate', now()->startOfMonth()->toDateString())->set('endDate', now()->endOfMonth()->toDateString())->set('alertPercentage', '75')->set('isActive', true)->call('save')->assertHasNoErrors()->assertRedirect(route('budgets'));

    $budget = Budget::query()->where('name', 'Monthly Food')->firstOrFail();
    expect($budget->user_id)->toBe($user->id)->and($budget->category_id)->toBe($category->id)->and($budget->is_active)->toBeTrue();
});

it('rejects invalid fields and categories not owned by the user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $otherCategory = Category::factory()->for($other)->create(['type' => CategoryType::Expense]);
    $incomeCategory = Category::factory()->for($user)->create(['type' => CategoryType::Income]);
    $this->actingAs($user);

    Livewire::test(Create::class)->set('name', '')->set('categoryId', (string) $otherCategory->id)->set('amount', '0')->set('period', 'invalid')->set('startDate', now()->addDay()->toDateString())->set('endDate', now()->toDateString())->set('alertPercentage', '101')->call('save')->assertHasErrors(['name', 'categoryId', 'amount', 'period', 'startDate', 'endDate', 'alertPercentage']);
    Livewire::test(Create::class)->set('name', 'Income')->set('categoryId', (string) $incomeCategory->id)->set('amount', '100')->call('save')->assertHasErrors(['categoryId']);
});
