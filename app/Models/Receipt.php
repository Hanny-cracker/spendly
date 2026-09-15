<?php

namespace App\Models;

use App\Concerns\BelongsToUser;
use App\Enums\ReceiptStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'transaction_id', 'original_path', 'original_filename', 'mime_type', 'merchant', 'receipt_date', 'total', 'currency', 'tax', 'payment_method', 'detected_category', 'raw_extraction', 'status', 'failure_reason', 'processed_at', 'confirmed_at'])]
class Receipt extends Model
{
    use BelongsToUser;

    protected function casts(): array
    {
        return ['status' => ReceiptStatus::class, 'total' => 'float', 'tax' => 'float', 'receipt_date' => 'date', 'raw_extraction' => 'array', 'processed_at' => 'datetime', 'confirmed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
