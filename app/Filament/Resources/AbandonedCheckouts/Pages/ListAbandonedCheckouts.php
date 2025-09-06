<?php

namespace App\Filament\Resources\AbandonedCheckouts\Pages;

use App\Filament\Resources\AbandonedCheckouts\AbandonedCheckoutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAbandonedCheckouts extends ListRecords
{
    protected static string $resource = AbandonedCheckoutResource::class;

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return AbandonedCheckoutResource::getModel()::query();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
