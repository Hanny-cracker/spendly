<?php

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('activates a subscription and records the authenticated admin', function (): void {
    $user = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);

    $subscription = app(SubscriptionService::class)->activate($user, $admin, SubscriptionPlan::Monthly, now(), now()->addMonth());

    expect($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->activated_by)->toBe($admin->id)
        ->and(app(SubscriptionService::class)->current($user)?->is($subscription))->toBeTrue();
});

it('keeps only one active subscription when activating again', function (): void {
    $user = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    $service = app(SubscriptionService::class);

    $service->activate($user, $admin, SubscriptionPlan::Monthly, now(), now()->addMonth());
    $service->activate($user, $admin, SubscriptionPlan::Annual, now(), now()->addYear());

    expect(Subscription::query()->where('user_id', $user->id)->where('status', SubscriptionStatus::Active)->count())->toBe(1);
});
