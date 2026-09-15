<?php

namespace App\Data\Receipt;

use Carbon\Carbon;

readonly class ReceiptExtractionData
{
    public function __construct(public ?string $merchant, public ?string $date, public ?string $currency, public ?float $total, public ?float $tax, public ?string $paymentMethod, public ?string $suggestedCategory, public array $items = []) {}

    /** @param array<string,mixed> $data */
    public static function fromArray(array $data): self
    {
        $items = array_values(array_filter(is_array($data['items'] ?? null) ? $data['items'] : [], fn ($item) => is_array($item) && is_string($item['name'] ?? null)));

        return new self(is_string($data['merchant'] ?? null) ? $data['merchant'] : null, is_string($data['date'] ?? null) && Carbon::hasFormat($data['date'], 'Y-m-d') ? $data['date'] : null, is_string($data['currency'] ?? null) ? strtoupper($data['currency']) : null, is_numeric($data['total'] ?? null) && (float) $data['total'] > 0 ? (float) $data['total'] : null, is_numeric($data['tax'] ?? null) ? (float) $data['tax'] : null, is_string($data['payment_method'] ?? null) ? $data['payment_method'] : null, is_string($data['suggested_category'] ?? null) ? $data['suggested_category'] : null, $items);
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return ['merchant' => $this->merchant, 'date' => $this->date, 'currency' => $this->currency, 'total' => $this->total, 'tax' => $this->tax, 'payment_method' => $this->paymentMethod, 'suggested_category' => $this->suggestedCategory, 'items' => $this->items];
    }
}
