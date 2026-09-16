<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\SubscriptionPlan;
use App\Models\User;
use App\Services\Subscriptions\SubscriptionService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('public_id')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('accounts_count')->counts('accounts')->label('Accounts'),
                TextColumn::make('transactions_count')->counts('transactions')->label('Transactions'),
                TextColumn::make('subscriptions.expires_at')->dateTime()->label('Subscription expires')->sortable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('activateSubscription')
                    ->label('Activate subscription')
                    ->form([
                        Select::make('plan')->options(collect(SubscriptionPlan::cases())->mapWithKeys(fn (SubscriptionPlan $plan): array => [$plan->value => ucfirst($plan->value)])->all())->required(),
                        DateTimePicker::make('expires_at')->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        app(SubscriptionService::class)->activate($record, auth()->user(), SubscriptionPlan::from($data['plan']), now(), new \DateTimeImmutable($data['expires_at']));
                    }),
            ]);
    }
}
