<?php

namespace App\Enums;

enum Feature: string
{
    case Accounts = 'accounts';
    case RecurringTransactions = 'recurring_transactions';
    case AIInsights = 'ai_insights';
    case ReceiptScanner = 'receipt_scanner';
    case AdvancedAnalytics = 'advanced_analytics';
    case Exports = 'exports';
}
