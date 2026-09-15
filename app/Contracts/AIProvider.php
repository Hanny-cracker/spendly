<?php

namespace App\Contracts;

use App\Data\AI\AIInsightData;
use App\Data\Receipt\ReceiptExtractionData;

interface AIProvider
{
    /** @param array<string, mixed> $context */
    public function generateStructured(string $instruction, array $context): array;

    public function generateFinancialInsight(array $analysis): AIInsightData;

    public function extractReceipt(string $imagePath, string $mimeType): ReceiptExtractionData;
}
