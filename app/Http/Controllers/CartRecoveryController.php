<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CartRecoveryController extends Controller
{
    public function recover(Request $request, string $token)
    {
        $cart = Cart::where('recovery_token', $token)->firstOrFail();

        if (!$cart->clicked_at) {
            $cart->clicked_at = now();
            $cart->recovered_via = $request->query('via', 'unknown'); // Puedes pasar ?via=email o ?via=whatsapp
            $cart->save();
        }

        // Actualiza el checkout en Shopify con el recovery_token en note
        if ($cart->shop && $cart->shopify_id) {
            Http::withHeaders([
                'X-Shopify-Access-Token' => $cart->shop->access_token,
            ])->put("https://{$cart->shop->shopify_domain}/admin/api/".config('services.shopify.api_version')."/checkouts/{$cart->shopify_id}.json", [
                'checkout' => [
                    'note' => 'recovery_token:' . $cart->recovery_token,
                ],
            ]);
        }

        // Redirige al checkout de Shopify o muestra una vista de recuperación
        return redirect($cart->abandoned_checkout_url ?? '/');
    }
}
