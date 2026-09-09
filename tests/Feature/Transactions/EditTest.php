<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the edit transaction page for its owner', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create();
    $transaction = Transaction::factory()->for($user)->for($account)->for($category)->create();

    $this->actingAs($user)
        ->get(route('transactions.edit', $transaction))
        ->assertOk()
        ->assertSee('Edit Transaction')
        ->assertSee('Update Transaction');
});

it('does not allow another user to edit a transaction', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $account = Account::factory()->for($owner)->create();
    $category = Category::factory()->for($owner)->create();
    $transaction = Transaction::factory()->for($owner)->for($account)->for($category)->create();

    $this->actingAs($otherUser)
        ->get(route('transactions.edit', $transaction))
        ->assertNotFound();
});
