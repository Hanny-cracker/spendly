<?php

namespace App\Contracts;

use App\Data\Payment\GatewayResponse;
use App\Models\Payment;

interface PaymentGateway
{
    public function initialize(Payment $payment): GatewayResponse;

    public function verify(Payment $payment): GatewayResponse;
}
