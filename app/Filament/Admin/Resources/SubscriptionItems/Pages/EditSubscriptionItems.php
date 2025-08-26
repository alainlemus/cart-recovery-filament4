<?php

namespace App\Filament\Admin\Resources\SubscriptionItems\Pages;

use App\Filament\Admin\Resources\SubscriptionItems\SubscriptionItemsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSubscriptionItems extends EditRecord
{
    protected static string $resource = SubscriptionItemsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
