<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyWebhookController extends Controller
{
    public function handleOrderCreate(Request $request)
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, config('services.shopify.api_secret'), true));

        if (!hash_equals($hmacHeader, $calculatedHmac)) {
            Log::warning('Shopify webhook signature mismatch');
            return response('Unauthorized', 401);
        }

        $payload = $request->json()->all();
        Log::info('Shopify Order Created Webhook Received', $payload);

        // Buscar el carrito por el checkout token o id de Shopify
        $cart = Cart::where('shopify_id', $payload['checkout_id'] ?? null)
            ->orWhere('id_cart', $payload['checkout_id'] ?? null)
            ->first();

        if ($cart) {
            $cart->status = 'completed';
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
        if (!hash_equals($hmacHeader, $calculatedHmac)) {
            Log::warning('Shopify webhook signature mismatch');
            return response('Unauthorized', 401);
        }

        $payload = $request->json()->all();
        Log::info('Shopify Checkout Created Webhook Received', $payload);

        // Registrar el checkout como abandonado si no existe
        $shop = Shop::where('shopify_domain', $payload['shop_domain'] ?? null)->first();

        if ($shop) {
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
                    'status' => 'abandoned',
                ]
            );
            Log::info('Abandoned checkout registered', ['shopify_id' => $payload['id']]);
        }

        return response('Webhook processed', 200);
    }
}
