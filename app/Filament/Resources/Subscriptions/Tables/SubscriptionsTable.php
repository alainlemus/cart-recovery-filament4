<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use App\Models\Subscription;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\View;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchable(false)
            ->paginated(false)
            ->description('Subscriptions associated with your account.')
            ->query(
                Subscription::query()->where('user_id', Auth::id())->where('stripe_status', 'active')
            )
            ->columns([
                TextColumn::make('product.name')->label('Product Name')->sortable()->searchable(),
                TextColumn::make('stripe_price')->label('Price')->sortable()->searchable()
                    ->formatStateUsing(fn($state) => '$' . number_format($state, 2)),
                TextColumn::make('ends_at')->label('Next Payment Date')->sortable()->searchable(),
                TextColumn::make('card_last_four')->label('Card Number')->sortable()->searchable()
                    ->formatStateUsing(fn($state) => '•••• •••• •••• ' . $state),
                TextColumn::make('created_at')->label('Created At')->dateTime('d/m/Y H:i')->sortable()->searchable(),
                TextColumn::make('updated_at')->label('Updated At')->dateTime('d/m/Y H:i')->sortable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ]);
    }
}
