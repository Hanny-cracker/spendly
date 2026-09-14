<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InsufficientAccountBalanceException extends ValidationException
{
    public function __construct(public readonly float $available, public readonly float $required)
    {
        $message = sprintf(
            'Insufficient account funds. %s FCFA is available, but %s FCFA is required.',
            number_format($available, 0),
            number_format($required, 0),
        );
        $validator = Validator::make([], []);
        $validator->errors()->add('amount', $message);
        parent::__construct($validator);
        $this->message = $message;
    }
}
