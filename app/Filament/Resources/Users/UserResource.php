<?php

namespace App\Filament\Resources\Users;

use App\Enums\Feature;
use App\Enums\RecurringStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Entitlements\EntitlementService;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profile')->schema([
                TextEntry::make('name'),
                TextEntry::make('email'),
                TextEntry::make('created_at')->dateTime()->label('Registered'),
                TextEntry::make('is_admin')->badge()->formatStateUsing(fn (bool $state): string => $state ? 'Administrator' : 'User'),
            ])->columns(2),
            Section::make('Financial overview')->schema([
                TextEntry::make('total_balance')->label('Current balance')->state(fn (User $record): string => number_format((float) $record->accounts()->sum('current_balance'), 2).' XAF'),
                TextEntry::make('opening_balance')->label('Opening balance')->state(fn (User $record): string => number_format((float) $record->accounts()->sum('opening_balance'), 2).' XAF'),
                TextEntry::make('accounts_count')->label('Accounts')->state(fn (User $record): int => $record->accounts()->count()),
                TextEntry::make('transactions_count')->label('Transactions')->state(fn (User $record): int => $record->transactions()->count()),
                TextEntry::make('income')->state(fn (User $record): string => number_format((float) Transaction::query()->where('user_id', $record->id)->where('status', TransactionStatus::Completed)->where('type', TransactionType::Income)->sum('amount'), 2).' XAF'),
                TextEntry::make('expenses')->state(fn (User $record): string => number_format((float) Transaction::query()->where('user_id', $record->id)->where('status', TransactionStatus::Completed)->where('type', TransactionType::Expense)->sum('amount'), 2).' XAF'),
                TextEntry::make('net_cash_flow')->label('Net cash flow')->state(function (User $record): string {
                    $query = Transaction::query()->where('user_id', $record->id)->where('status', TransactionStatus::Completed);
                    $income = (float) (clone $query)->where('type', TransactionType::Income)->sum('amount');
                    $expenses = (float) (clone $query)->where('type', TransactionType::Expense)->sum('amount');

                    return number_format($income - $expenses, 2).' XAF';
                }),
            ])->columns(3),
            Section::make('Subscription')->schema([
                TextEntry::make('subscription')->label('Latest subscription')->state(function (User $record): string {
                    $subscription = $record->subscriptions()->latest('created_at')->first();

                    return $subscription ? ucfirst($subscription->plan->value).' - '.ucfirst($subscription->status->value) : 'None';
                }),
                TextEntry::make('subscription_expires')->label('Expires')->state(fn (User $record): string => ($record->subscriptions()->latest('created_at')->first()?->expires_at)?->format('d M Y H:i') ?? 'N/A'),
                TextEntry::make('subscription_history')->label('History')->state(fn (User $record): string => $record->subscriptions()->latest('created_at')->limit(5)->get(['plan', 'status', 'created_at'])->map(fn ($subscription): string => ucfirst($subscription->plan->value).' - '.ucfirst($subscription->status->value).' ('.$subscription->created_at->format('d M Y').')')->implode("\n") ?: 'None')->listWithLineBreaks()->columnSpanFull(),
            ])->columns(2),
            Section::make('Payments')->schema([
                TextEntry::make('payment_history')->label('Recent payments')->state(fn (User $record): string => Payment::query()->where('user_id', $record->id)->latest('created_at')->limit(10)->get(['external_reference', 'amount', 'currency', 'status', 'created_at'])->map(fn ($payment): string => ($payment->external_reference ?: 'No reference').' - '.number_format((float) $payment->amount, 2).' '.$payment->currency.' - '.ucfirst($payment->status->value))->implode("\n") ?: 'None')->listWithLineBreaks()->columnSpanFull(),
            ]),
            Section::make('Accounts')->schema([
                TextEntry::make('account_summary')->label('Accounts')->state(fn (User $record): string => $record->accounts()->get(['name', 'current_balance'])->map(fn ($account): string => $account->name.' ('.number_format((float) $account->current_balance, 2).' XAF)')->implode('; ') ?: 'None')->columnSpanFull(),
            ]),
            Section::make('Transaction activity')->schema([
                TextEntry::make('income_count')->label('Income count')->state(fn (User $record): int => $record->transactions()->where('type', TransactionType::Income)->count()),
                TextEntry::make('expense_count')->label('Expense count')->state(fn (User $record): int => $record->transactions()->where('type', TransactionType::Expense)->count()),
                TextEntry::make('transfer_count')->label('Transfer count')->state(fn (User $record): int => $record->transactions()->where('type', TransactionType::Transfer)->count()),
                TextEntry::make('recent_transactions')->label('Recent transactions')->state(fn (User $record): string => $record->transactions()->latest('date')->limit(10)->get(['date', 'title', 'amount', 'type'])->map(fn ($transaction): string => $transaction->date->format('d M Y').' - '.$transaction->title.' ('.number_format((float) $transaction->amount, 2).' XAF)')->implode("\n") ?: 'None')->listWithLineBreaks()->columnSpanFull(),
            ])->columns(3),
            Section::make('Recurring transactions')->schema([
                TextEntry::make('recurring_total')->label('Schedules')->state(fn (User $record): int => $record->recurringTransactions()->count()),
                TextEntry::make('recurring_active')->label('Active schedules')->state(fn (User $record): int => $record->recurringTransactions()->where('status', RecurringStatus::Active)->count()),
                TextEntry::make('recurring_next')->label('Next run')->state(fn (User $record): string => ($record->recurringTransactions()->where('status', RecurringStatus::Active)->min('next_run')) ?? 'None'),
            ])->columns(3),
            Section::make('Feature usage')->schema([
                TextEntry::make('accounts_usage')->label('Accounts')->state(fn (User $record): string => self::usage($record, Feature::Accounts)),
                TextEntry::make('recurring_usage')->label('Recurring')->state(fn (User $record): string => self::usage($record, Feature::RecurringTransactions)),
                TextEntry::make('ai_usage')->label('AI insights this month')->state(fn (User $record): string => self::usage($record, Feature::AIInsights)),
                TextEntry::make('receipt_usage')->label('Receipt scans this month')->state(fn (User $record): string => self::usage($record, Feature::ReceiptScanner)),
            ])->columns(4),
        ]);
    }

    private static function usage(User $user, Feature $feature): string
    {
        $result = app(EntitlementService::class)->check($user, $feature);

        return $result->limit === null ? $result->usage.' / Unlimited' : $result->usage.' / '.$result->limit;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
