<?php

namespace App\Filament\Admin\Resources\SubscriptionItems\Pages;

use App\Filament\Admin\Resources\SubscriptionItems\SubscriptionItemsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionItems extends ListRecords
{
    protected static string $resource = SubscriptionItemsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
