<?php

use App\Http\Controllers\CartRecoveryController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ShopifyAuthController;
use App\Http\Controllers\ShopifyBillingController;
use App\Http\Controllers\ShopifyRegistrationController;
use App\Http\Controllers\ShopifyWebhookController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Webhooks\ShopifyBillingWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

// Language switcher
Route::get('/locale/{locale}', [LocaleController::class, 'change'])->name('locale.change');

// Stripe subscription routes (for non-Shopify users)
Route::get('/subscription/create/{plan}', [SubscriptionController::class, 'create'])->name('subscription.create');
Route::post('/subscription/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
Route::get('/select-plan/{user}', [SubscriptionController::class, 'selectPlan'])->name('select.plan');
Route::post('/subscription/renew/{plan}', [SubscriptionController::class, 'renewSubscription'])->name('subscription.renew');

// Shopify registration routes (new integrated flow)
Route::get('/shopify/register/{product}', [ShopifyRegistrationController::class, 'show'])->name('shopify.register');
Route::post('/shopify/register/{product}', [ShopifyRegistrationController::class, 'store'])->name('shopify.register.store');

// Shopify OAuth routes
Route::get('/shopify/auth/{shop_id}', [ShopifyAuthController::class, 'auth'])->name('shopify.auth');
Route::get('/shopify/callback', [ShopifyAuthController::class, 'callback'])->name('shopify.callback');
Route::get('/recover-cart/{token}', [CartRecoveryController::class, 'recover'])->name('cart.recover');

// Shopify Billing routes
// Plans page - accessible without auth (users can see plans before registering)
Route::get('/shopify/billing/plans', [ShopifyBillingController::class, 'plans'])->name('shopify.billing.plans');

// Subscribe route needs to be accessible for automatic flow after OAuth
Route::post('/shopify/billing/subscribe/{product}', [ShopifyBillingController::class, 'subscribe'])
    ->middleware(['auth'])
    ->name('shopify.billing.subscribe');

// These routes require authentication
Route::middleware(['auth'])->prefix('shopify/billing')->name('shopify.billing.')->group(function () {
    Route::get('/callback', [ShopifyBillingController::class, 'callback'])->name('callback');
    Route::post('/cancel', [ShopifyBillingController::class, 'cancel'])->name('cancel');
    Route::post('/sync', [ShopifyBillingController::class, 'sync'])->name('sync');
});

// Webhooks (no auth required)
Route::post('/webhooks/orders/create', [ShopifyWebhookController::class, 'handleOrderCreate']);
Route::post('/webhooks/checkouts/create', [ShopifyWebhookController::class, 'handleCheckoutCreate']);
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);
Route::post('/webhooks/shopify/billing', [ShopifyBillingWebhookController::class, 'handle']);

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
