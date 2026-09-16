<?php

namespace App\Data\Payment;

readonly class GatewayResponse
{
    public function __construct(public bool $accepted, public ?string $providerReference = null, public ?string $message = null) {}
}
