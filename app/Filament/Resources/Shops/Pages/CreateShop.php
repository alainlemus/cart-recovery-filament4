<?php

namespace App\Filament\Resources\Shops\Pages;

use App\Filament\Resources\Shops\ShopResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreateShop extends CreateRecord
{
    protected static string $resource = ShopResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('Datos recibidos en CreateShop antes de mutar:', [
            'data' => $data,
            'context' => 'CreateShop::mutateFormDataBeforeCreate'
        ]);

        $data = parent::mutateFormDataBeforeCreate($data);

        Log::info('Datos después de mutar en CreateShop:', [
            'data' => $data,
            'context' => 'CreateShop::mutateFormDataBeforeCreate'
        ]);

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $user = Auth::user();

        if (!$user) {
            Log::error('No hay usuario autenticado en handleRecordCreation', [
                'data' => $data,
                'context' => 'CreateShop::handleRecordCreation'
            ]);
            throw new \Exception('No hay usuario autenticado.');
        }

        // Obtener la última suscripción activa, seleccionando solo los campos necesarios
        $lastSubscription = $user->subscriptions()->where('stripe_status', 'active')->latest()->first(['id', 'product_id']);

        $data['user_id'] = $user->id;
        $data['subscription_id'] = $lastSubscription?->id;
        $data['product_id'] = $lastSubscription?->product_id; // Ajusta según el campo correcto

        Log::info('Datos finales para crear el registro:', [
            'data' => $data,
            'context' => 'CreateShop::handleRecordCreation'
        ]);

        return parent::handleRecordCreation($data);
    }

}
