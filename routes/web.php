<?php

use App\Http\Controllers\CartRecoveryController;
use App\Http\Controllers\ShopifyAuthController;
use App\Http\Controllers\ShopifyWebhookController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/subscription/create/{plan}', [SubscriptionController::class, 'create'])->name('subscription.create');
Route::post('/subscription/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
Route::get('/select-plan/{user}', [SubscriptionController::class, 'selectPlan'])->name('select.plan');
Route::post('/subscription/renew/{plan}', [SubscriptionController::class, 'renewSubscription'])->name('subscription.renew');

// shopify routes
Route::get('/shopify/auth/{shop_id}', [ShopifyAuthController::class, 'auth'])->name('shopify.auth');
Route::get('/shopify/callback', [ShopifyAuthController::class, 'callback'])->name('shopify.callback');
Route::get('/recover-cart/{token}', [CartRecoveryController::class, 'recover'])->name('cart.recover');

// webhooks shopify
Route::post('/webhooks/orders/create', [ShopifyWebhookController::class, 'handleOrderCreate']);
Route::post('/webhooks/checkouts/create', [ShopifyWebhookController::class, 'handleCheckoutCreate']);
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});*/

// Route::stripeWebhooks('webhook/stripe');

require __DIR__.'/auth.php';
