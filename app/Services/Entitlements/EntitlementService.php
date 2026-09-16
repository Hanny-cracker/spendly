<?php

namespace App\Services\Entitlements;

use App\Data\Entitlements\EntitlementResult;
use App\Enums\Feature;
use App\Enums\ReceiptStatus;
use App\Enums\RecurringStatus;
use App\Models\AIInsight;
use App\Models\Receipt;
use App\Models\User;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Support\Carbon;

class EntitlementService
{
    public function __construct(private SubscriptionService $subscriptions) {}

    public function can(User $user, Feature $feature): bool
    {
        return $this->check($user, $feature)->allowed;
    }

    public function limit(User $user, Feature $feature): ?int
    {
        return $this->check($user, $feature)->limit;
    }

    public function usage(User $user, Feature $feature): int
    {
        return match ($feature) {
            Feature::Accounts => $user->accounts()->count(),
            Feature::RecurringTransactions => $user->recurringTransactions()->where('status', RecurringStatus::Active)->count(),
            Feature::AIInsights => $user->hasMany(AIInsight::class)->where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
            Feature::ReceiptScanner => $user->hasMany(Receipt::class)->whereIn('status', [ReceiptStatus::Processed, ReceiptStatus::Confirmed])->where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
            Feature::AdvancedAnalytics, Feature::Exports => 0,
        };
    }

    public function remaining(User $user, Feature $feature): ?int
    {
        $limit = $this->limit($user, $feature);

        return $limit === null ? null : max(0, $limit - $this->usage($user, $feature));
    }

    public function hasReachedLimit(User $user, Feature $feature): bool
    {
        return ($this->remaining($user, $feature) ?? 1) === 0;
    }

    public function check(User $user, Feature $feature): EntitlementResult
    {
        $usage = $this->usage($user, $feature);
        $premium = $this->subscriptions->hasEntitlementAccess($user);
        $tier = $premium ? 'premium' : 'free';
        $definition = config("features.{$tier}.{$feature->value}", ['enabled' => true, 'limit' => null]);
        $enabled = (bool) ($definition['enabled'] ?? true);
        $limit = $definition['limit'] ?? null;
        $remaining = $limit === null ? null : max(0, $limit - $usage);

        return new EntitlementResult(
            allowed: $enabled && ($remaining === null || $remaining > 0),
            feature: $feature,
            limit: $limit === null ? null : (int) $limit,
            usage: $usage,
            remaining: $remaining,
            reason: ! $enabled ? 'This feature is available with Premium.' : ($remaining === 0 ? 'You have reached the Free plan limit.' : null),
        );
    }
}
