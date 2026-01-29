<?php

declare(strict_types=1);

use App\Mail\CartRecoveryMail;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;

/*
it('shows only the user\'s abandoned checkouts', function () {
    $user = User::factory()->create(['role' => 'super-admin']);
    $shop = \App\Models\Shop::factory()->for($user, 'user')->create([
        'name' => 'Test Shop',
        'access_token' => 'token',
        'shopify_domain' => 'test.myshopify.com',
    ]);
    $cart = Cart::factory()->for($shop, 'shop')->create([
        'user_id' => $user->id,
        'email_client' => 'test@example.com',
        'status' => 'abandoned',
    ]);
    actingAs($user);

    $response = test()->get('/admin/abandoned-checkouts');
    $response->assertOk();
    $response->assertSee($cart->email_client);
});
*/

it('sends recovery email with correct link', function () {
    Mail::fake();
    $user = User::factory()->create(['role' => 'super-admin']);
    $shop = \App\Models\Shop::factory()->for($user, 'user')->create([
        'name' => 'Test Shop',
        'access_token' => 'token',
        'shopify_domain' => 'test.myshopify.com',
    ]);
    $cart = Cart::factory()->for($shop, 'shop')->create([
        'user_id' => $user->id,
        'recovery_token' => 'test-token',
        'email_client' => 'test@example.com',
        'status' => 'abandoned',
    ]);
    actingAs($user);

    $recoveryUrl = route('cart.recover', [
        'token' => $cart->recovery_token,
        'via' => 'email',
    ]);

    Mail::to($cart->email_client)->send(new CartRecoveryMail([
        'email' => $cart->email_client,
        'description' => 'Test message',
        'checkout_url' => $recoveryUrl,
        'total_price' => $cart->total_price,
        'currency' => $cart->currency,
    ]));

    Mail::assertSent(CartRecoveryMail::class, function ($mail) use ($recoveryUrl) {
        return str_contains($mail->data['checkout_url'], $recoveryUrl);
    });
});

it('sends WhatsApp message if phone is present', function () {
    $user = User::factory()->create(['role' => 'super-admin']);
    $shop = \App\Models\Shop::factory()->for($user, 'user')->create([
        'name' => 'Test Shop',
        'access_token' => 'token',
        'shopify_domain' => 'test.myshopify.com',
    ]);
    $cart = Cart::factory()->for($shop, 'shop')->create([
        'user_id' => $user->id,
        'phone_client' => '+521234567890',
        'status' => 'abandoned',
    ]);
    actingAs($user);
    expect($cart->phone_client)->toBe('+521234567890');
});
