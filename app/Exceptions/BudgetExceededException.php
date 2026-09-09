<?php

namespace App\Exceptions;

use App\Data\Budget\BudgetAvailabilityData;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BudgetExceededException extends ValidationException
{
    public function __construct(public readonly BudgetAvailabilityData $availability)
    {
        $message = sprintf(
            'Insufficient %s budget. %s FCFA remaining.',
            $availability->categoryName,
            number_format($availability->remaining, 0),
        );
        $validator = Validator::make([], []);
        $validator->errors()->add('amount', $message);

        parent::__construct($validator);
        $this->message = $message;
    }
}
