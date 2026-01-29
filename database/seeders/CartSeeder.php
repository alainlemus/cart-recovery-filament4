<?php

namespace Database\Seeders;

use App\Models\Cart;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cart::factory()->create([
            'id_cart' => '123456789',
            'user_id' => 2, // Assuming user with ID 3 exists
            'shop_id' => 1, // Assuming shop with ID 1 exists
            'shopify_id' => 'abc123',
            'response' => '[{algo=1},{algo2=1}]',
            'total_price' => 49.97,
            'abandoned_at' => now()->subDays(1),
            'abandoned_checkout_url' => 'https://demoshop.myshopify.com/checkout/abc123',
            'email_client' => 'Mailgun',
            'phone_client' => 'Twilio',
        ]);
    }
}
