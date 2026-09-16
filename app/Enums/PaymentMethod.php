<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case MtnMomo = 'mtn_momo';
    case OrangeMoney = 'orange_money';
}
