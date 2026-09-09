<?php

use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

it('requires authentication', function () {
    $this->get(route('budgets'))->assertRedirect(route('login'));
});

it('renders user budgets with analysis summaries and statuses', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $account = Account::withoutGlobalScopes()->where('user_id', $user->id)->first();
    $warningCategory = Category::factory()->for($user)->create(['name' => 'Food', 'type' => CategoryType::Expense]);
    $overCategory = Category::factory()->for($user)->create(['name' => 'Transport', 'type' => CategoryType::Expense]);
    $warning = Budget::factory()->for($user)->for($warningCategory)->create(['name' => 'Food Plan', 'amount' => 100000, 'alert_percentage' => 80]);
    $over = Budget::factory()->for($user)->for($overCategory)->create(['name' => 'Transport Plan', 'amount' => 100000]);
    Budget::factory()->for($other)->create(['name' => 'Private Budget', 'amount' => 999999]);
    Transaction::factory()->for($user)->create(['account_id' => $account->id, 'category_id' => $warningCategory->id, 'amount' => 85000, 'date' => now(), 'status' => 'completed', 'type' => 'expense']);
    Transaction::factory()->for($user)->create(['account_id' => $account->id, 'category_id' => $overCategory->id, 'amount' => 110000, 'date' => now(), 'status' => 'completed', 'type' => 'expense']);

    $this->actingAs($user)->get(route('budgets'))->assertOk()
        ->assertSee('Food Plan')->assertSee('Transport Plan')->assertDontSee('Private Budget')
        ->assertSee('200,000')->assertSee('195,000')->assertSee('5,000')
        ->assertSee('85.0%')->assertSee('110.0%')->assertSee('warning')->assertSee('over budget');
});

it('renders the empty state and create action', function () {
    $user = User::factory()->create();
    Budget::withoutGlobalScopes()->where('user_id', $user->id)->delete();
    $this->actingAs($user)->get(route('budgets'))->assertOk()->assertSee('No budgets yet')->assertSee(route('budgets.create'));
});
