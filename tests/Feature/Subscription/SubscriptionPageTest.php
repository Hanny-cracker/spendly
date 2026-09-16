<?php

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the free plan and usage to a free user', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('subscription'))
        ->assertOk()->assertSee('Free Plan')->assertSee('Accounts')->assertSee('3 / 5');
});

it('shows unlimited usage for an active subscriber', function (): void {
    $user = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    app(SubscriptionService::class)->activate($user, $admin, SubscriptionPlan::Monthly, now(), now()->addMonth());

    $this->actingAs($user)->get(route('subscription'))
        ->assertOk()->assertSee('Monthly')->assertSee('Unlimited');
});

it('shows trial and effective free state correctly', function (): void {
    $user = User::factory()->create();
    Subscription::factory()->for($user)->create(['status' => SubscriptionStatus::Trial, 'expires_at' => now()->addDays(5)]);
    $this->actingAs($user)->get(route('subscription'))->assertOk()->assertSee('Trial');

    $expired = User::factory()->create();
    Subscription::factory()->for($expired)->create(['status' => SubscriptionStatus::Active, 'expires_at' => now()->subDay()]);
    $this->actingAs($expired)->get(route('subscription'))->assertOk()->assertSee('Free Plan');
});

it('requires authentication', function (): void {
    $this->get(route('subscription'))->assertRedirect(route('login'));
});
