<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionPlan;
use App\Models\Payment;
use App\Models\User;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(private PaymentGateway $gateway, private SubscriptionService $subscriptions) {}

    public function createPayment(User $user, SubscriptionPlan $plan, PaymentMethod $method, string $phone): Payment
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';
        if (preg_match('/^6[0-9]{8}$/', $phone)) {
            $phone = '237'.$phone;
        }
        if (! preg_match('/^2376[0-9]{8}$/', $phone)) {
            throw ValidationException::withMessages(['phone' => 'Enter a valid Cameroon mobile number.']);
        }
        $pricing = config("payments.plans.{$plan->value}");
        if (! is_array($pricing)) {
            throw ValidationException::withMessages(['plan' => 'This subscription plan is unavailable.']);
        }

        $payment = Payment::query()->create([
            'user_id' => $user->id,
            'subscription_plan' => $plan,
            'provider' => PaymentProvider::Aggregator,
            'payment_method' => $method,
            'amount' => $pricing['amount'],
            'currency' => $pricing['currency'],
            'status' => PaymentStatus::Pending,
            'external_reference' => (string) Str::uuid(),
            'phone_number' => $phone,
            'expires_at' => now()->addMinutes(30),
        ]);
        $response = $this->gateway->initialize($payment);
        if ($response->accepted) {
            $payment->update(['status' => PaymentStatus::Processing, 'provider_reference' => $response->providerReference]);
        }

        return $payment->refresh();
    }

    public function markSuccessful(Payment $payment, string $providerReference): Payment
    {
        return DB::transaction(function () use ($payment, $providerReference): Payment {
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($locked->status === PaymentStatus::Successful) {
                return $locked;
            }
            if (in_array($locked->status, [PaymentStatus::Failed, PaymentStatus::Cancelled, PaymentStatus::Expired], true)) {
                throw ValidationException::withMessages(['payment' => 'This payment cannot be completed.']);
            }
            $locked->update(['status' => PaymentStatus::Successful, 'provider_reference' => $providerReference, 'paid_at' => now()]);
            $pricing = config("payments.plans.{$locked->subscription_plan->value}");
            $this->subscriptions->activate($locked->user, null, $locked->subscription_plan, now(), now()->addDays($pricing['days']), (string) $locked->amount, $locked->provider_reference, null, $locked->id);

            return $locked->refresh();
        });
    }

    public function markFailed(Payment $payment, string $reason): Payment
    {
        if ($payment->status === PaymentStatus::Successful) {
            return $payment;
        }
        $payment->update(['status' => PaymentStatus::Failed, 'failure_reason' => $reason, 'failed_at' => now()]);

        return $payment->refresh();
    }
}
