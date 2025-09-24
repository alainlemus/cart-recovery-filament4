<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartRecoveryController extends Controller
{
    public function recover(Request $request, string $token)
    {
        $cart = Cart::where('recovery_token', $token)->firstOrFail();

        if (!$cart->recovered_at) {
            $cart->recovered_at = now();
            $cart->recovered_via = $request->query('via', 'unknown'); // Puedes pasar ?via=email o ?via=whatsapp
            $cart->save();
        }

        // Redirige al checkout de Shopify o muestra una vista de recuperación
        return redirect($cart->abandoned_checkout_url ?? '/');
    }
}
