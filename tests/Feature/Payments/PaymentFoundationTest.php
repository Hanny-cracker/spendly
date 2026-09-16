<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionPlan;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('initializes a mobile money payment using trusted pricing', function (): void {
    $user = User::factory()->create();
    $payment = app(PaymentService::class)->createPayment($user, SubscriptionPlan::Monthly, PaymentMethod::MtnMomo, '+237 650 123 456');

    expect($payment->status)->toBe(PaymentStatus::Processing)
        ->and((float) $payment->amount)->toBe(5000.0)
        ->and($payment->currency)->toBe('XAF');
});

it('rejects invalid mobile numbers', function (): void {
    expect(fn () => app(PaymentService::class)->createPayment(User::factory()->create(), SubscriptionPlan::Monthly, PaymentMethod::OrangeMoney, '123'))->toThrow(ValidationException::class);
});

it('processes a signed successful webhook idempotently', function (): void {
    config(['payments.webhook_secret' => 'secret']);
    $user = User::factory()->create();
    $payment = app(PaymentService::class)->createPayment($user, SubscriptionPlan::Monthly, PaymentMethod::MtnMomo, '650123456');
    $payload = ['external_reference' => $payment->external_reference, 'status' => 'successful', 'amount' => 5000, 'currency' => 'XAF'];
    $signature = hash_hmac('sha256', json_encode($payload), 'secret');

    $this->postJson('/webhooks/payments/aggregator', $payload, ['X-Payment-Signature' => $signature])->assertOk();
    $this->postJson('/webhooks/payments/aggregator', $payload, ['X-Payment-Signature' => $signature])->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::Successful)->and($user->subscriptions()->count())->toBe(1);
});
