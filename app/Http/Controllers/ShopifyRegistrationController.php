<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class ShopifyRegistrationController extends Controller
{
    /**
     * Show the registration form for Shopify users.
     */
    public function show(Request $request, Product $product)
    {
        return view('shopify.register', [
            'product' => $product,
        ]);
    }

    /**
     * Handle the registration process for Shopify users.
     * Creates user + shop in one transaction, then initiates OAuth.
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'shopify_domain' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/',
                'unique:shops,shopify_domain',
            ],
        ], [
            'shopify_domain.regex' => 'The Shopify domain must be in the format: yourstore.myshopify.com',
            'shopify_domain.unique' => 'This Shopify store is already registered.',
        ]);

        try {
            // Create user and shop in a transaction
            [$user, $shop] = DB::transaction(function () use ($validated, $product) {
                // Create the user
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'admin',
                ]);

                Log::info('Shopify registration: User created', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);

                // Create the shop linked to the user
                $shop = Shop::create([
                    'user_id' => $user->id,
                    'name' => $validated['name']."'s Shop",
                    'shopify_domain' => $validated['shopify_domain'],
                    'product_id' => $product->id, // Pre-assign the selected product
                ]);

                Log::info('Shopify registration: Shop created', [
                    'shop_id' => $shop->id,
                    'shopify_domain' => $shop->shopify_domain,
                    'product_id' => $product->id,
                ]);

                return [$user, $shop];
            });

            // Login the user
            Auth::login($user);

            // Store the intended subscription plan in session
            session([
                'intended_subscription_product_id' => $product->id,
                'registration_completed' => true,
            ]);

            Log::info('Shopify registration: User logged in, redirecting to OAuth', [
                'user_id' => $user->id,
                'shop_id' => $shop->id,
            ]);

            // Redirect to OAuth flow
            return redirect()->route('shopify.auth', ['shop_id' => $shop->id])
                ->with('success', 'Account created! Now connect your Shopify store.');
        } catch (\Exception $e) {
            Log::error('Shopify registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Registration failed. Please try again.');
        }
    }
}
