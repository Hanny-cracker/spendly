<?php

namespace App\Livewire\Subscription;

use App\Enums\Feature;
use App\Enums\PaymentMethod;
use App\Enums\SubscriptionPlan;
use App\Services\Entitlements\EntitlementService;
use App\Services\Payments\PaymentService;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public string $plan = 'monthly';

    public string $paymentMethod = 'mtn_momo';

    public string $phone = '';

    public ?string $paymentMessage = null;

    public function beginPayment(PaymentService $payments): void
    {
        $this->validate(['plan' => ['required', 'in:monthly,annual'], 'paymentMethod' => ['required', 'in:mtn_momo,orange_money'], 'phone' => ['required', 'string']]);
        $payment = $payments->createPayment(auth()->user(), SubscriptionPlan::from($this->plan), PaymentMethod::from($this->paymentMethod), $this->phone);
        $this->paymentMessage = $payment->status->value === 'processing' ? 'Payment initiated. Complete the mobile-money prompt to finish activation.' : 'Payment is pending provider confirmation.';
    }

    public function render(EntitlementService $entitlements, SubscriptionService $subscriptions): View
    {
        $user = auth()->user();
        $latest = $subscriptions->latest($user);
        $active = $subscriptions->hasEntitlementAccess($user);
        $usage = collect([Feature::Accounts, Feature::RecurringTransactions, Feature::AIInsights, Feature::ReceiptScanner])
            ->mapWithKeys(fn (Feature $feature): array => [$feature->value => $entitlements->check($user, $feature)->toArray()]);

        return view('livewire.subscription.index', [
            'latest' => $latest,
            'active' => $active,
            'usage' => $usage,
            'daysRemaining' => $latest?->expires_at?->diffInDays(now(), false),
            'free' => config('features.free'),
        ])->layout('layouts.app', ['title' => 'Subscription | Spendly', 'header' => 'Subscription']);
    }
}
