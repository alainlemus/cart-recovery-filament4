<?php

namespace App\Filament\Resources\AbandonedCheckouts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;

class AbandonedCheckoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('Checkout ID'),
                TextColumn::make('email_client')->label('Email'),
                TextColumn::make('line_items')->label('Productos'),
                TextColumn::make('total_price')->label('Total')->money('MXN'),
                TextColumn::make('created_at')->label('Creado')->dateTime(),
                TextColumn::make('abandoned_checkout_url')->label('URL de Recuperación')->url('abandoned_checkout_url'),
            ])
            ->paginated(false) // Shopify API ya pagina
            ->filters([
                //
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
