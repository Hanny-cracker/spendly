<?php

namespace App\Enums;

enum ReceiptStatus: string
{
    case Processing = 'processing';
    case Processed = 'processed';
    case Confirmed = 'confirmed';
    case Failed = 'failed';
}
