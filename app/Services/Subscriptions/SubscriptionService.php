<?php

namespace App\Services\Subscriptions;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function activate(User $user, ?User $admin, SubscriptionPlan $plan, DateTimeInterface $startsAt, ?DateTimeInterface $expiresAt, ?string $amount = null, ?string $paymentReference = null, ?string $notes = null, ?int $paymentId = null): Subscription
    {
        return DB::transaction(function () use ($user, $admin, $plan, $startsAt, $expiresAt, $amount, $paymentReference, $notes, $paymentId): Subscription {
            Subscription::query()->where('user_id', $user->id)->whereIn('status', [SubscriptionStatus::Trial->value, SubscriptionStatus::Active->value])->update(['status' => SubscriptionStatus::Cancelled]);

            return Subscription::query()->create([
                'user_id' => $user->id,
                'plan' => $plan,
                'status' => SubscriptionStatus::Active,
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'activated_at' => now(),
                'activated_by' => $admin?->id,
                'amount' => $amount,
                'payment_reference' => $paymentReference,
                'notes' => $notes,
                'payment_id' => $paymentId,
            ]);
        });
    }

    public function current(User $user): ?Subscription
    {
        return $user->subscriptions()->where('status', SubscriptionStatus::Active)->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->latest('expires_at')->first();
    }

    public function latest(User $user): ?Subscription
    {
        return $user->subscriptions()->latest('created_at')->first();
    }

    public function hasEntitlementAccess(User $user): bool
    {
        return $user->subscriptions()
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trial])
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function cancel(Subscription $subscription): void
    {
        $subscription->update(['status' => SubscriptionStatus::Cancelled]);
    }

    public function renew(Subscription $subscription, DateTimeInterface $expiresAt): Subscription
    {
        $subscription->update(['status' => SubscriptionStatus::Active, 'expires_at' => $expiresAt]);

        return $subscription->refresh();
    }
}
