<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopifyAuthController extends Controller
{
    public function auth(Request $request, $shop_id)
    {
        $user = Auth::user();

        if (!$user) {
            Log::error('No hay usuario autenticado en Shopify auth', [
                'shop_id' => $shop_id,
                'context' => 'ShopifyAuthController::auth'
            ]);
            return redirect()->route('filament.admin-shop.auth.login')->with('error', 'Debes estar autenticado.');
        }

        $shop = Shop::where('id', $shop_id)->where('user_id', $user->id)->first();

        if (!$shop) {
            Log::error('Tienda no encontrada o no pertenece al usuario', [
                'shop_id' => $shop_id,
                'user_id' => $user->id,
                'context' => 'ShopifyAuthController::auth'
            ]);
            return redirect()->route('filament.admin-shop.resources.shops.index')->with('error', 'Tienda no encontrada.');
        }

        if (!empty($shop->access_token)) {
            Log::info('La tienda ya tiene un access_token', [
                'shop_id' => $shop_id,
                'context' => 'ShopifyAuthController::auth'
            ]);
            return redirect()->route('filament.admin-shop.resources.shops.index')->with('success', 'La tienda ya está conectada.');
        }

        // Parámetros para la URL de autorización de Shopify
        $api_key = config('services.shopify.api_key');
        $scopes = config('services.shopify.scopes'); // Ajusta los permisos según tus necesidades
        $redirect_uri = config('services.shopify.redirect_uri'); // Debe coincidir con la URL configurada en la app de Shopify
        $state = Str::random(40); // Generar un state único para CSRF

        // Guardar el state en la sesión para validarlo en el callback
        $request->session()->put('shopify_state', $state);
        $request->session()->put('shop_id', $shop_id);
        $request->session()->put('shopify_domain', $shop->shopify_domain);

        // Construir la URL de autorización
        $auth_url = "https://{$shop->shopify_domain}/admin/oauth/authorize?" . http_build_query([
            'client_id' => $api_key,
            'scope' => $scopes,
            'redirect_uri' => $redirect_uri,
            'state' => $state,
        ]);

        Log::info('Redirigiendo a Shopify para autenticación', [
            'api_key' => $api_key,
            'scopes' => $scopes,
            'redirect_uri' => $redirect_uri,
            'shopify_domain' => $shop->shopify_domain,
            'auth_url' => $auth_url,
            'context' => 'ShopifyAuthController::auth'
        ]);

        if (filter_var($auth_url, FILTER_VALIDATE_URL)) {
            return redirect()->away($auth_url); // usa away para URLs externas
        } else {
            Log::error('URL de Shopify inválida', ['auth_url' => $auth_url]);
            return redirect()->route('filament.admin-shop.resources.shops.index')
                ->with('error', 'URL de Shopify inválida.');
        }

    }

    public function callback(Request $request)
    {
        $state = $request->query('state');
        $shop_id = $request->session()->get('shop_id');
        $stored_state = $request->session()->get('shopify_state');

        Log::info('Callback de Shopify recibido', [
            'state' => $state,
            'stored_state' => $stored_state,
            'shop_id' => $shop_id,
            'context' => 'ShopifyAuthController::callback'
        ]);

        // Validar el state para prevenir CSRF
        if (!$state || $state !== $stored_state) {
            Log::error('Estado inválido en el callback de Shopify', [
                'state' => $state,
                'stored_state' => $stored_state,
                'context' => 'ShopifyAuthController::callback'
            ]);
            //return redirect()->route('filament.admin-shop.resources.shops.index')->with('error', 'Error de seguridad: estado inválido.');
        }

        $shop = Shop::find($shop_id);

        if (!$shop) {
            Log::error('Tienda no encontrada en el callback', [
                'shop_id' => $shop_id,
                'context' => 'ShopifyAuthController::callback'
            ]);
            return redirect()->route('filament.admin-shop.resources.shops.index')->with('error', 'Tienda no encontrada.');
        }

        // Obtener el access_token
        $response = Http::post("https://{$shop->shopify_domain}/admin/oauth/access_token", [
            'client_id' => config('services.shopify.api_key'),
            'client_secret' => config('services.shopify.api_secret'),
            'code' => $request->query('code'),
        ]);

        if ($response->failed()) {
            Log::error('Error al obtener el access_token de Shopify', [
                'shop_id' => $shop_id,
                'response' => $response,
                'context' => 'ShopifyAuthController::callback'
            ]);
            return redirect()->route('filament.admin-shop.resources.shops.index')->with('error', 'Error al conectar con Shopify.');
        }

        $data = $response->json();
        $access_token = $data['access_token'] ?? null;

        if (!$access_token) {
            Log::error('No se recibió access_token en el callback', [
                'shop_id' => $shop_id,
                'response' => $data,
                'context' => 'ShopifyAuthController::callback'
            ]);
            return redirect()->route('filament.admin-shop.resources.shops.index')->with('error', 'No se pudo obtener el token de acceso.');
        }

        // Guardar el access_token en la tienda
        $shop->update(['access_token' => $access_token]);

        Log::info('Access_token guardado exitosamente', [
            'shop_id' => $shop_id,
            'context' => 'ShopifyAuthController::callback'
        ]);

        // Limpiar la sesión
        $request->session()->forget(['shopify_state', 'shop_id']);

        return redirect()->route('filament.admin-shop.resources.shops.index')->with('success', 'Tienda conectada con Shopify exitosamente.');
    }
}
