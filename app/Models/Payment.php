<?php

namespace App\Models;

use App\Concerns\BelongsToUser;
use App\Concerns\HasPublicIdentifier;
use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionPlan;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['public_id', 'user_id', 'subscription_plan', 'provider', 'payment_method', 'amount', 'currency', 'status', 'provider_reference', 'external_reference', 'phone_number', 'paid_at', 'failed_at', 'expires_at', 'failure_reason', 'metadata'])]
class Payment extends Model
{
    use BelongsToUser;

    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    use HasPublicIdentifier;

    protected const PUBLIC_ID_PREFIX = 'pay';

    protected function casts(): array
    {
        return ['subscription_plan' => SubscriptionPlan::class, 'provider' => PaymentProvider::class, 'payment_method' => PaymentMethod::class, 'status' => PaymentStatus::class, 'amount' => 'decimal:2', 'metadata' => 'array', 'paid_at' => 'datetime', 'failed_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
