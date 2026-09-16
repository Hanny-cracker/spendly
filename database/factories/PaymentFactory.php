<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionPlan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => 'pay_'.fake()->unique()->bothify('################'),
            'user_id' => User::factory(),
            'subscription_plan' => SubscriptionPlan::Monthly,
            'provider' => PaymentProvider::Aggregator,
            'payment_method' => PaymentMethod::MtnMomo,
            'amount' => 5000,
            'currency' => 'XAF',
            'status' => PaymentStatus::Pending,
            'external_reference' => fake()->unique()->uuid(),
            'phone_number' => '+2376'.fake()->numerify('########'),
        ];
    }
}
