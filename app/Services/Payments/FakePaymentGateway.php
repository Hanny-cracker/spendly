<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Data\Payment\GatewayResponse;
use App\Models\Payment;

class FakePaymentGateway implements PaymentGateway
{
    public function initialize(Payment $payment): GatewayResponse
    {
        return new GatewayResponse(true, 'fake_'.$payment->external_reference, 'Test payment initialized.');
    }

    public function verify(Payment $payment): GatewayResponse
    {
        return new GatewayResponse(true, $payment->provider_reference);
    }
}
