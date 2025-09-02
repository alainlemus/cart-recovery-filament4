<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Checkout\Session;

class SubscriptionController extends Controller
{

    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function create($planId)
    {
        $infoPlan = Product::find($planId);
        return view('subscription.create', ['infoProduct' => $infoPlan]);
    }

    public function checkout(Request $request, $plan)
    {
        // Validar datos
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Crear usuario si no existe
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'admin',
        ]);

        Log::info("Usuario creado: " . $user->email);

        Auth::login($user);

        // Obtener el objeto Product
        $plan = Product::findOrFail($plan);

        try {

            Stripe::setApiKey(config('cashier.secret')); // tu clave de Stripe

            // Crear cliente y sesión de Stripe
            $stripeCustomer = Customer::create([
                'name' => $user->name,
                'email' => $user->email,
            ]);

            $stripeCustomerId = $stripeCustomer->id;

            $session = Session::create([
                'customer' => $stripeCustomerId,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'USD',
                        'product_data' => [
                            'name' => $plan->name,
                        ],
                        'unit_amount' => intval($plan->price * 100), // en centavos
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('subscription.success', ['user' => $user->id]). '&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('subscription.create', ['plan' => $plan->id]),
            ]);

            // Guardar en tabla subscriptions como pendiente
            Subscription::create([
                'user_id' => $user->id,
                'product_id' => $plan->id,
                'type' => 'CARD',
                'stripe_id' => $session->id,
                'stripe_status' => 'pending',
                'stripe_price' => $plan->price,
                'quantity' => 1,
                'start_at' => now(),
                'ends_at' => now()->addMonth(1), // o calcula según tu plan
            ]);

            // Redirigir al checkout de Stripe
            return redirect($session->url);

        } catch (Exception $e) {
            // Registrar el error para debugging
            Log::error('Error al crear la suscripción en Stripe: '.$e->getMessage());

            // Eliminar el usuario recién registrado
            $user->delete();

            // Redirigir al usuario con mensaje de error
            return redirect()->route('subscription.create', ['plan' => $plan->id])
                ->with('error', 'Hubo un problema al procesar tu suscripción. Por favor, inténtalo de nuevo.');
        }
    }

    public function success(Request $request)
    {
        Log::info("Respuesta de registro de suscripcion en STRIPE: " . $request);

        $user = Auth::user();

        $stripeSessionId = $request->query('session_id');

        // Buscar la suscripción en tu tabla usando el session_id
        $subscription = Subscription::where('stripe_id', $stripeSessionId)->firstOrFail();

        // Actualizar estado
        $subscription->update([
            'stripe_status' => 'active',
            'start_at' => now(),
            'ends_at' => null, // o calcula según tu plan
        ]);

        Log::info("Suscripción activada: " . $subscription->stripe_id);

        // Redirigir al panel de Filament
        return redirect()->to('http://cart-recovery.test/admin-shop')
            ->with('success', '¡Suscripción activada! Bienvenido a tu panel.');
    }

}
