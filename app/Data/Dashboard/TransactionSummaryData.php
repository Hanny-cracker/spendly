<?php

namespace App\Data\Dashboard;

readonly class TransactionSummaryData
{
    public function __construct(
        public int $id,
        public string $publicId,
        public string $title,
        public ?string $categoryName,
        public ?string $categoryIcon,
        public ?string $categoryColor,
        public string $accountName,
        public string $date,
        public float $amount,
        public string $type,
        public string $status,
        public bool $isTransfer,
    ) {}

    /**
     * @return array<string, bool|float|int|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->publicId,
            'title' => $this->title,
            'category_name' => $this->categoryName,
            'category_icon' => $this->categoryIcon,
            'category_color' => $this->categoryColor,
            'account_name' => $this->accountName,
            'date' => $this->date,
            'amount' => $this->amount,
            'type' => $this->type,
            'status' => $this->status,
            'is_transfer' => $this->isTransfer,
        ];
    }
}
