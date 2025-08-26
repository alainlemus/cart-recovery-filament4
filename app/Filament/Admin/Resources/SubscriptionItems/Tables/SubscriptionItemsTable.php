<?php

namespace App\Filament\Admin\Resources\SubscriptionItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables;

class SubscriptionItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subscription.type')
                    ->label('Subscription Type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stripe_id')
                    ->label('Stripe ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stripe_product')
                    ->label('Stripe Product')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stripe_price')
                    ->label('Stripe Price')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subscription_id')
                    ->relationship('subscription', 'type')
                    ->label('Subscription')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
