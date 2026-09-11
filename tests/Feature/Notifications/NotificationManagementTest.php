<?php

use App\Enums\CategoryType;
use App\Enums\RecurringTransactionNotificationType;
use App\Livewire\Notifications\Dropdown;
use App\Models\Budget;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\RecurringTransactionNotification;
use App\Services\RecurringTransactions\RecurringTransactionNotificationService;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;

function databaseNotification(User $user, bool $read = false): string
{
    $id = Str::uuid()->toString();
    DB::table('notifications')->insert([
        'id' => $id,
        'type' => RecurringTransactionNotification::class,
        'notifiable_type' => $user->getMorphClass(),
        'notifiable_id' => $user->id,
        'data' => json_encode(['notification_title' => 'Recurring expense recorded', 'title' => 'Internet', 'message' => 'Recorded successfully.']),
        'read_at' => $read ? now() : null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}

it('marks one or all owned notifications as read without deleting them', function () {
    $user = User::factory()->create();
    $first = databaseNotification($user);
    databaseNotification($user);

    Livewire::actingAs($user)->test(Dropdown::class)
        ->assertSee('Internet')
        ->call('markAsRead', $first)
        ->assertHasNoErrors();

    expect($user->notifications()->findOrFail($first)->read_at)->not->toBeNull()
        ->and($user->notifications()->count())->toBe(2)
        ->and($user->unreadNotifications()->count())->toBe(1);

    Livewire::actingAs($user)->test(Dropdown::class)->call('markAllAsRead')->assertHasNoErrors();

    expect($user->notifications()->count())->toBe(2)
        ->and($user->unreadNotifications()->count())->toBe(0);
});

it('clears one notification and derives the unread count from remaining records', function () {
    $user = User::factory()->create();
    $first = databaseNotification($user);
    databaseNotification($user);
    databaseNotification($user);

    Livewire::actingAs($user)->test(Dropdown::class)
        ->assertSee('3 unread notifications')
        ->call('clear', $first)
        ->assertSee('2 unread notifications');

    expect($user->notifications()->count())->toBe(2)
        ->and($user->unreadNotifications()->count())->toBe(2);
});

it('cannot manipulate another users notification and clear all remains owner scoped', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    databaseNotification($user);
    $otherNotification = databaseNotification($other);

    Livewire::actingAs($user)->test(Dropdown::class)
        ->call('markAsRead', $otherNotification)
        ->call('clear', $otherNotification)
        ->call('clearAll')
        ->assertSee('New notifications will appear here.');

    expect($user->notifications()->count())->toBe(0)
        ->and($other->notifications()->count())->toBe(1)
        ->and($other->unreadNotifications()->count())->toBe(1);
});

it('clears only notification history without changing financial state', function () {
    $user = User::factory()->create();
    $account = $user->accounts()->firstOrFail();
    $account->update(['current_balance' => 75000]);
    $category = $user->categories()->where('type', CategoryType::Expense)->firstOrFail();
    $budget = Budget::factory()->for($user)->for($category)->create(['amount' => 100000]);
    $schedule = RecurringTransaction::factory()->for($user)->create(['account_id' => $account->id, 'category_id' => $category->id, 'next_run' => now()->addDay()]);
    $transaction = Transaction::factory()->for($user)->for($account)->for($category)->expense()->create(['recurring_transaction_id' => $schedule->id, 'amount' => 25000]);
    $notification = databaseNotification($user);
    $nextRun = $schedule->next_run->toDateTimeString();

    Livewire::actingAs($user)->test(Dropdown::class)->call('clear', $notification);

    expect($user->notifications()->count())->toBe(0)
        ->and($transaction->fresh())->not->toBeNull()
        ->and($account->refresh()->current_balance)->toBe(75000.0)
        ->and($budget->refresh()->amount)->toBe(100000.0)
        ->and($schedule->refresh()->next_run->toDateTimeString())->toBe($nextRun);
});

it('does not redeliver a reminder after its notification is cleared', function () {
    $user = User::factory()->create();
    $account = $user->accounts()->firstOrFail();
    $category = $user->categories()->where('type', CategoryType::Expense)->firstOrFail();
    $schedule = RecurringTransaction::factory()->for($user)->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'next_run' => now()->addHours(6),
        'last_6h_notified_at' => now(),
    ]);
    databaseNotification($user);

    Livewire::actingAs($user)->test(Dropdown::class)->call('clearAll');

    Notification::fake();
    $this->artisan('transactions:notify-upcoming')->assertSuccessful();
    Notification::assertNothingSent();
    expect($schedule->refresh()->last_6h_notified_at)->not->toBeNull();
});

it('allows future queued notifications after notification history is cleared', function () {
    Queue::fake();
    $user = User::factory()->create();
    $schedule = RecurringTransaction::factory()->for($user)->create();
    databaseNotification($user);

    Livewire::actingAs($user)->test(Dropdown::class)->call('clearAll');
    app(RecurringTransactionNotificationService::class)->generated($schedule, now()->addDay());

    Queue::assertPushed(SendQueuedNotifications::class, fn (SendQueuedNotifications $job): bool => $job->notification instanceof RecurringTransactionNotification
        && $job->notification->type === RecurringTransactionNotificationType::Generated);
});
