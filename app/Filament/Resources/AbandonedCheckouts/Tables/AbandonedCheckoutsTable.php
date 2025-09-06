<?php

namespace App\Filament\Resources\AbandonedCheckouts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AbandonedCheckoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')->label('Cliente')->sortable()->searchable(),
                TextColumn::make('line_items')->label('Productos'),
                TextColumn::make('total_price')->label('Total'),
                TextColumn::make('created_at')->label('Fecha')->dateTime(),
                TextColumn::make('abandoned_checkout_url')->label('Link')->url(fn($record) => $record['abandoned_checkout_url'], true),
            ])
            ->paginated(false) // Shopify API ya pagina
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
