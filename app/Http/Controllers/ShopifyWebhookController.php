<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopifyWebhookController extends Controller
{
    public function handleOrderCreate(Request $request)
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, config('services.shopify.api_secret'), true));

        if (! hash_equals($hmacHeader, $calculatedHmac)) {
            Log::warning('Shopify webhook signature mismatch');

            return response('Unauthorized', 401);
        }

        $payload = $request->json()->all();
        Log::info('Shopify Order Created Webhook Received', $payload);

        if (isset($payload['id']) && $payload['id'] === 'exampleOrderId') {
            Log::info('Se recibió una orden de prueba, no se procesa', ['response' => $payload]);

            return response('Test order received', 200);
        }

        // Buscar por checkout_id o recovery_token en note
        $cart = null;
        if (! empty($payload['note']) && str_starts_with($payload['note'], 'recovery_token:')) {
            $token = str_replace('recovery_token:', '', $payload['note']);
            $cart = Cart::where('recovery_token', $token)->first();
        }

        if (! $cart) {
            $cart = Cart::where('shopify_id', $payload['checkout_id'] ?? null)
                ->orWhere('id_cart', $payload['checkout_id'] ?? null)
                ->first();
        }

        if ($cart) {
            $cart->status = 'complete';
            $cart->save();
            Log::info('Cart marked as completed', ['cart_id' => $cart->id]);
        }

        return response('Webhook processed', 200);
    }

    public function handleCheckoutCreate(Request $request)
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, config('services.shopify.webhook_secret'), true));

        Log::info('HMAC Verification', [
            'hmacHeader' => $hmacHeader,
            'calculatedHmac' => $calculatedHmac,
            'data' => $data,
        ]);
        if (! hash_equals($hmacHeader, $calculatedHmac)) {
            Log::warning('Shopify webhook signature mismatch');

            return response('Unauthorized', 401);
        }

        $payload = $request->json()->all();
        Log::info('Shopify Checkout Created Webhook Received', $payload);

        if (isset($payload['id']) && $payload['id'] === 'exampleCartId') {
            Log::info('Se recibió un carrito de prueba, no se guarda en BD', ['response' => $payload]);

            return response('Test cart received', 200);
        }

        // Registrar el checkout como abandonado si no existe
        $shop = Shop::where('shopify_domain', $payload['shop_domain'] ?? null)->first();

        if ($shop) {

            $existingCart = Cart::where('shopify_id', $payload['id'])->first();

            Cart::updateOrCreate(
                ['shopify_id' => $payload['id']],
                [
                    'shop_id' => $shop->id,
                    'user_id' => $shop->user_id,
                    'id_cart' => $payload['id'],
                    'email' => $payload['email'] ?? null,
                    'response' => $payload,
                    'total_price' => $payload['total_price'] ?? 0,
                    'abandoned_at' => $payload['created_at'] ?? now(),
                    'abandoned_checkout_url' => $payload['abandoned_checkout_url'] ?? null,
                    'recovery_token' => $existingCart?->recovery_token ?? Str::uuid(),
                    'status' => 'abandoned',
                ]
            );
            Log::info('Abandoned checkout registered', ['shopify_id' => $payload['id']]);

        }

        return response('Webhook processed', 200);
    }
}
