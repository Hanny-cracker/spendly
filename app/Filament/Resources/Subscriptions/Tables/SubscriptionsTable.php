<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')->label('User')->searchable(),
                TextColumn::make('plan')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('starts_at')->dateTime()->sortable(),
                TextColumn::make('expires_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(collect(SubscriptionStatus::cases())->mapWithKeys(fn (SubscriptionStatus $status): array => [$status->value => ucfirst($status->value)])->all()),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('renew')
                    ->form([DateTimePicker::make('expires_at')->required()])
                    ->action(fn (Subscription $record, array $data): Subscription => app(SubscriptionService::class)->renew($record, new \DateTimeImmutable($data['expires_at']))),
                Action::make('cancel')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->action(function (Subscription $record): void {
                        app(SubscriptionService::class)->cancel($record);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
