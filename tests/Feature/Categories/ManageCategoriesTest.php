<?php

use App\Enums\CategoryType;
use App\Livewire\Categories\Edit;
use App\Livewire\Categories\Index;
use App\Models\Budget;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

it('allows the owner to edit an unused category including its type', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create(['name' => 'Groceries', 'type' => CategoryType::Expense]);
    $this->actingAs($user);
    Livewire::test(Edit::class, ['category' => $category])->set('name', 'Food & Groceries')->set('type', 'income')->set('color', '#2563EB')->set('icon', 'briefcase')->call('save')->assertHasNoErrors();
    expect($category->fresh()->name)->toBe('Food & Groceries')->and($category->fresh()->type)->toBe(CategoryType::Income)->and($category->fresh()->color)->toBe('#2563EB')->and($category->fresh()->user_id)->toBe($user->id);
});

it('protects edit from guests and other users', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->for($owner)->create();
    $this->get(route('categories.edit', $category))->assertRedirect(route('login'));
    $this->actingAs($other)->get(route('categories.edit', $category))->assertNotFound();
});

it('blocks changing the type of a category used by financial records', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    Transaction::factory()->for($user)->create(['category_id' => $category->id]);
    $this->actingAs($user);
    Livewire::test(Edit::class, ['category' => $category])->set('type', 'income')->call('save')->assertSet('domainError', "This category type can't be changed because the category is already used by financial records.");
    expect($category->fresh()->type)->toBe(CategoryType::Expense);
});

it('deletes an unused category after confirmation', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();
    $this->actingAs($user);
    Livewire::test(Index::class)->call('confirmDeletion', $category->public_id)->call('delete');
    expect($category->fresh())->toBeNull();
});

it('blocks deleting categories used by transactions budgets or recurring schedules', function (string $dependency, string $message) {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create(['type' => CategoryType::Expense]);
    if ($dependency === 'transaction') {
        Transaction::factory()->for($user)->create(['category_id' => $category->id]);
    }
    if ($dependency === 'budget') {
        Budget::factory()->for($user)->for($category)->create();
    }
    if ($dependency === 'recurring') {
        RecurringTransaction::factory()->for($user)->create(['category_id' => $category->id]);
    }
    $this->actingAs($user);
    Livewire::test(Index::class)->call('confirmDeletion', $category->public_id)->call('delete')->assertSet('deletionError', $message);
    expect($category->fresh())->not->toBeNull();
})->with([
    ['transaction', "This category can't be deleted because it has transaction history."],
    ['budget', "This category can't be deleted because it is used by a budget."],
    ['recurring', "This category can't be deleted because it is used by a recurring transaction."],
]);

it('reauthorizes malicious index deletion calls', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->for($owner)->create();
    $this->actingAs($other);
    expect(fn () => Livewire::test(Index::class)->call('confirmDeletion', $category->public_id))
        ->toThrow(ModelNotFoundException::class);
    expect($category->fresh())->not->toBeNull();
});
