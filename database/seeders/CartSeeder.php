<?php

namespace Database\Seeders;

use App\Models\Cart;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cart::factory()->create([
            'user_id' => 2, // Assuming user with ID 3 exists
            'shop_id' => 1, // Assuming shop with ID 1 exists
            'items' => json_encode([
                ['product_id' => 1, 'quantity' => 2, 'price' => 19.99],
                ['product_id' => 2, 'quantity' => 1, 'price' => 9.99],
            ]),
            'total_price' => 49.97,
            'status' => 'abandoned',
            'abandoned_at' => now()->subDays(1),
            'email_client' => 'Mailgun',
            'phone_client' => 'Twilio',
        ]);
    }
}
