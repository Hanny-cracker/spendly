<?php

use App\Enums\CategoryType;
use App\Livewire\Budgets\Edit;
use App\Livewire\Budgets\Show;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

it('shows analyzed progress and only contributing spending to the owner', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->for($user)->create(['name' => 'Food', 'type' => CategoryType::Expense]);
    $otherCategory = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $budget = Budget::factory()->for($user)->for($category)->create(['name' => 'Monthly Food', 'amount' => 100000, 'alert_percentage' => 80]);
    $account = Account::withoutGlobalScopes()->where('user_id', $user->id)->first();
    Transaction::factory()->for($user)->create(['account_id' => $account->id, 'category_id' => $category->id, 'title' => 'Groceries', 'amount' => 85000, 'date' => now(), 'status' => 'completed']);
    Transaction::factory()->for($user)->create(['account_id' => $account->id, 'category_id' => $otherCategory->id, 'title' => 'Unrelated', 'date' => now()]);
    Transaction::factory()->for($other)->create(['title' => 'Private spending']);
    $this->actingAs($user)->get(route('budgets.show', $budget))->assertOk()->assertSee('Monthly Food')->assertSee('85.0% used')->assertSee('15,000 FCFA remaining')->assertSee('warning')->assertSee('Groceries')->assertDontSee('Unrelated')->assertDontSee('Private spending');
});

it('protects show and edit pages', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $budget = Budget::factory()->for($owner)->create();
    $this->get(route('budgets.show', $budget))->assertRedirect(route('login'));
    $this->actingAs($other)->get(route('budgets.show', $budget))->assertNotFound();
    $this->actingAs($other)->get(route('budgets.edit', $budget))->assertNotFound();
});

it('updates budget settings without changing transactions', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $budget = Budget::factory()->for($user)->for($category)->create();
    $transaction = Transaction::factory()->for($user)->create(['category_id' => $category->id, 'amount' => 1234]);
    $this->actingAs($user);
    Livewire::test(Edit::class, ['budget' => $budget])->set('name', 'Updated Budget')->set('amount', '300000')->set('alertPercentage', '75')->call('save')->assertHasNoErrors();
    expect($budget->fresh()->name)->toBe('Updated Budget')->and($budget->fresh()->amount)->toBe(300000.0)->and($budget->fresh()->alert_percentage)->toBe(75)->and($transaction->fresh()->amount)->toBe(1234.0);
});

it('toggles active state and preserves overlap rules', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $budget = Budget::factory()->for($user)->for($category)->create(['is_active' => true]);
    $this->actingAs($user);
    Livewire::test(Show::class, ['budget' => $budget])->call('toggleStatus');
    expect($budget->fresh()->is_active)->toBeFalse();
    Livewire::test(Show::class, ['budget' => $budget])->call('toggleStatus');
    expect($budget->fresh()->is_active)->toBeTrue();
});

it('deletes only the planning object', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    $budget = Budget::factory()->for($user)->for($category)->create();
    $account = Account::withoutGlobalScopes()->where('user_id', $user->id)->first();
    $transaction = Transaction::factory()->for($user)->create(['account_id' => $account->id, 'category_id' => $category->id]);
    $balance = $account->current_balance;
    $this->actingAs($user);
    Livewire::test(Show::class, ['budget' => $budget])->call('confirmDeletion')->assertSet('confirmingDeletion', true)->call('delete')->assertRedirect(route('budgets'));
    expect($budget->fresh())->toBeNull()->and($category->fresh())->not->toBeNull()->and($transaction->fresh())->not->toBeNull()->and($account->fresh()->current_balance)->toBe($balance);
});
