<?php

use App\Console\Commands\NotifyUpcomingRecurringTransactions;
use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use App\Enums\RecurringTransactionNotificationType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\RecurringTransactionNotification;
use App\Services\RecurringTransactions\RecurringTransactionNotificationService;
use App\Services\RecurringTransactions\RecurringTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('sends a notification after a recurring transaction is generated', function () {

    Notification::fake();

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
        'current_balance' => 500000,
    ]);

    $category = Category::factory()->create([
        'user_id' => $user->id,
    ]);

    $recurring = RecurringTransaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'title' => 'Monthly Rent',
        'amount' => 250000,
        'type' => TransactionType::Expense,
        'frequency' => RecurringFrequency::Monthly,
        'interval' => 1,
        'start_date' => '2026-01-01',
        'next_run' => now(),
        'status' => RecurringStatus::Active,
    ]);

    $transaction = app(RecurringTransactionService::class)
        ->generate($recurring);

    expect($transaction)
        ->toBeInstanceOf(
            Transaction::class
        );

    expect($transaction->status)
        ->toBe(TransactionStatus::Completed);

    Notification::assertSentTo(
        $user,
        RecurringTransactionNotification::class
    );
});
it('sends a 24 hour notification', function () {

    Notification::fake();

    $user = User::factory()->create();

    $recurring = RecurringTransaction::factory()->create([
        'user_id' => $user->id,

        'next_run' => now()->addHours(20),

        'status' => RecurringStatus::Active,

        'last_24h_notified_at' => null,
        'last_6h_notified_at' => null,
    ]);

    app(NotifyUpcomingRecurringTransactions::class)
        ->handle(
            app(RecurringTransactionNotificationService::class)
        );

    Notification::assertSentTo(
        $user,
        RecurringTransactionNotification::class,
        function ($notification) {
            return $notification->type
                === RecurringTransactionNotificationType::Upcoming24Hours;
        }
    );
});

it('sends a 6 hour notification', function () {

    Notification::fake();

    $user = User::factory()->create();

    $recurring = RecurringTransaction::factory()->create([
        'user_id' => $user->id,

        // schedule the next run 6 hours from now so a 6-hour notification is triggered
        'next_run' => now()->addHours(6),

        'status' => RecurringStatus::Active,

        'last_24h_notified_at' => now()->subHours(2),

        'last_6h_notified_at' => null,
    ]);

    app(NotifyUpcomingRecurringTransactions::class)
        ->handle(
            app(RecurringTransactionNotificationService::class)
        );

    Notification::assertSentTo(
        $user,
        RecurringTransactionNotification::class,
        function ($notification) {
            return $notification->type
                === RecurringTransactionNotificationType::Upcoming6Hours;
        }
    );
});

it('does not send duplicate 24 hour notifications', function () {

    Notification::fake();

    $user = User::factory()->create();

    $recurring = RecurringTransaction::factory()->create([
        'user_id' => $user->id,

        'next_run' => now()->addHours(20),

        'status' => RecurringStatus::Active,

        'last_24h_notified_at' => now(),
    ]);

    app(NotifyUpcomingRecurringTransactions::class)
        ->handle(
            app(RecurringTransactionNotificationService::class)
        );

    Notification::assertNothingSent();
});

it('does not send duplicate 12 hour notifications', function () {

    Notification::fake();

    $user = User::factory()->create();

    $recurring = RecurringTransaction::factory()->create([
        'user_id' => $user->id,

        'next_run' => now()->addHours(8),

        'status' => RecurringStatus::Active,

        'last_6h_notified_at' => now(),
    ]);

    app(NotifyUpcomingRecurringTransactions::class)
        ->handle(
            app(RecurringTransactionNotificationService::class)
        );

    Notification::assertNothingSent();
});

it('resets notification timestamps after generating a recurring transaction', function () {

    Notification::fake();

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
    ]);

    $category = Category::factory()->create([
        'user_id' => $user->id,
    ]);

    $recurring = RecurringTransaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,

        'next_run' => now(),

        'status' => RecurringStatus::Active,

        'last_24h_notified_at' => now()->subHours(2),

        'last_6h_notified_at' => now()->subHours(1),
    ]);

    app(RecurringTransactionService::class)
        ->generate($recurring);

    $recurring->refresh();

    expect($recurring->last_24h_notified_at)
        ->toBeNull();

    expect($recurring->last_12h_notified_at)
        ->toBeNull();

    expect($recurring->last_generated_at)
        ->not->toBeNull();
});
it('marks recurring transaction as completed after its final occurrence', function () {

    Notification::fake();

    $user = User::factory()->create();

    $account = Account::factory()->create([
        'user_id' => $user->id,
    ]);

    $category = Category::factory()->create([
        'user_id' => $user->id,
    ]);

    $recurring = RecurringTransaction::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,

        'next_run' => now(),

        'end_date' => today(),

        'status' => RecurringStatus::Active,
    ]);

    app(RecurringTransactionService::class)
        ->generate($recurring);

    $recurring->refresh();

    expect($recurring->status)
        ->toBe(RecurringStatus::Completed);
});
