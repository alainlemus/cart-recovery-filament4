<?php

namespace App\Filament\Resources\AbandonedCheckouts\Pages;

use App\Filament\Resources\AbandonedCheckouts\AbandonedCheckoutResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAbandonedCheckout extends CreateRecord
{
    protected static string $resource = AbandonedCheckoutResource::class;
}
