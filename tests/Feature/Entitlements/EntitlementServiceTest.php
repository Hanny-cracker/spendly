<?php

use App\Enums\Feature;
use App\Enums\SubscriptionPlan;
use App\Models\User;
use App\Services\Entitlements\EntitlementService;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('provides unlimited access without a subscription while subscriptions are being finalized', function (): void {
    $user = User::factory()->create();
    $service = app(EntitlementService::class);

    expect($service->usage($user, Feature::Accounts))->toBe(3)
        ->and($service->limit($user, Feature::Accounts))->toBeNull()
        ->and($service->remaining($user, Feature::Accounts))->toBeNull()
        ->and($service->can($user, Feature::AdvancedAnalytics))->toBeTrue();
});

it('treats an active subscription as premium with unlimited features', function (): void {
    $user = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    app(SubscriptionService::class)->activate($user, $admin, SubscriptionPlan::Monthly, now(), now()->addMonth());

    expect(app(EntitlementService::class)->limit($user, Feature::Accounts))->toBeNull()
        ->and(app(EntitlementService::class)->can($user, Feature::AIInsights))->toBeTrue();
});

it('treats expired and cancelled subscriptions as free access', function (): void {
    $user = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    $subscriptionService = app(SubscriptionService::class);
    $subscription = $subscriptionService->activate($user, $admin, SubscriptionPlan::Monthly, now()->subMonth(), now()->subDay());
    $subscription->update(['status' => 'cancelled']);

    expect($subscriptionService->hasEntitlementAccess($user))->toBeFalse()
        ->and(app(EntitlementService::class)->limit($user, Feature::Accounts))->toBeNull();
});

it('supports unlimited features and reached-limit checks', function (): void {
    $user = User::factory()->create();
    $service = app(EntitlementService::class);

    expect($service->hasReachedLimit($user, Feature::AdvancedAnalytics))->toBeFalse()
        ->and($service->remaining($user, Feature::AdvancedAnalytics))->toBeNull()
        ->and($service->can($user, Feature::AdvancedAnalytics))->toBeTrue();
});
