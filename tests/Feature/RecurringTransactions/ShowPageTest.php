<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Services\RecurringTransactions\RecurringTransactionService;

it('protects show and renders only linked generated history newest first', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $account = Account::factory()->for($user)->create(['current_balance' => 100000]);
    $category = Category::factory()->for($user)->expense()->create();
    $schedule = RecurringTransaction::factory()->for($user)->for($account)->for($category)->create(['title' => 'Internet Subscription', 'amount' => 25000]);
    $otherSchedule = RecurringTransaction::factory()->for($user)->for($account)->for($category)->create(['title' => 'Other Schedule']);
    $service = app(RecurringTransactionService::class);
    $first = $service->generate($schedule);
    $schedule->update(['next_run' => now()]);
    $second = $service->generate($schedule->refresh());
    $foreign = $service->generate($otherSchedule);

    $this->get(route('recurring.show', $schedule))->assertRedirect(route('login'));
    $this->actingAs($other)->get(route('recurring.show', $schedule))->assertNotFound();
    $response = $this->actingAs($user)->get(route('recurring.show', $schedule));
    $response->assertOk()->assertSee('Internet Subscription')->assertSee($first->public_id)->assertSee($second->public_id)->assertDontSee($foreign->public_id)->assertSeeInOrder([$second->public_id, $first->public_id]);
});
