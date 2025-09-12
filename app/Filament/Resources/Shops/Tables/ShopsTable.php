<?php

namespace App\Filament\Resources\Shops\Tables;

use App\Models\Shop;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShopsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Shop::query())
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('user_id', auth()->id());
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Shop Name')
                    ->searchable(),
                TextColumn::make('shopify_domain')
                    ->searchable(),
                TextColumn::make('access_token')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                EditAction::make(),
                Action::make('connect_shopify')
                    ->label('Connect with Shopify')
                    ->icon('heroicon-o-link')
                    ->url(function ($record) {
                        return route('shopify.auth', ['shop_id' => $record->id]);
                    })
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->disabled(function ($record) {
                        // Deshabilitar si ya tiene un access_token
                        return !empty($record->access_token);
                    })
                    ->tooltip(function ($record) {
                        return !empty($record->access_token) ? 'The store is now connected to Shopify.' : 'Connect your store with Shopify.';
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
