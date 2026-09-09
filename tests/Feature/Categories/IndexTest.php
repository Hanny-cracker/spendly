<?php

use App\Enums\CategoryType;
use App\Livewire\Categories\Index;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

it('requires authentication', function () {
    $this->get(route('categories'))->assertRedirect(route('login'));
});

it('groups owned categories and renders efficient usage counts', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Category::withoutGlobalScopes()->whereIn('user_id', [$user->id, $other->id])->delete();
    $expense = Category::factory()->for($user)->create(['name' => 'Groceries', 'type' => CategoryType::Expense]);
    $income = Category::factory()->for($user)->create(['name' => 'Side Business', 'type' => CategoryType::Income]);
    Category::factory()->for($other)->create(['name' => 'Private Category']);
    Transaction::factory()->count(2)->for($user)->create(['category_id' => $expense->id]);
    Budget::factory()->for($user)->for($expense)->create();
    $this->actingAs($user);

    Livewire::test(Index::class)->assertSee('Groceries')->assertSee('2 transactions')->assertSee('1 budget')->assertDontSee('Private Category')
        ->call('setType', 'income')->assertSee('Side Business')->assertDontSee('Groceries');
});

it('renders type-specific empty states and add action', function () {
    $user = User::factory()->create();
    Category::withoutGlobalScopes()->where('user_id', $user->id)->delete();
    $this->actingAs($user);
    Livewire::test(Index::class)->assertSee('No expense categories')->assertSee(route('categories.create'))->call('setType', 'income')->assertSee('No income categories');
});
