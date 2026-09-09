<?php

use App\Livewire\Categories\Create;
use App\Models\Category;
use App\Models\User;
use Livewire\Livewire;

it('protects create from guests', function () {
    $this->get(route('categories.create'))->assertRedirect(route('login'));
});

it('creates owned expense and income categories', function (string $type, string $name) {
    $user = User::factory()->create();
    $this->actingAs($user);
    Livewire::test(Create::class)->set('name', $name)->set('type', $type)->set('color', '#047857')->set('icon', 'wallet')->call('save')->assertHasNoErrors();
    $category = Category::query()->where('name', $name)->firstOrFail();
    expect($category->user_id)->toBe($user->id)->and($category->type->value)->toBe($type);
})->with([['expense', 'Groceries'], ['income', 'Side Business']]);

it('validates name type and duplicates per user and type', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Livewire::test(Create::class)->set('name', '')->set('type', 'invalid')->call('save')->assertHasErrors(['name', 'type']);
    Category::factory()->for($user)->create(['name' => 'Food', 'type' => 'expense']);
    Livewire::test(Create::class)->set('name', 'Food')->set('type', 'expense')->call('save')->assertHasErrors(['name']);
});
