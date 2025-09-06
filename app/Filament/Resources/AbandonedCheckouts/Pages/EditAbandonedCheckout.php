<?php

namespace App\Filament\Resources\AbandonedCheckouts\Pages;

use App\Filament\Resources\AbandonedCheckouts\AbandonedCheckoutResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAbandonedCheckout extends EditRecord
{
    protected static string $resource = AbandonedCheckoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
