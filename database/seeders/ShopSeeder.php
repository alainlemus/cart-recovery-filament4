<?php

namespace Database\Seeders;

use App\Models\Shop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Shop::factory()->create([
            'name' => 'Demo Shop',
            'access_token' => 'demo_access_token_123456',
            'shopify_domain' => 'demoshop.myshopify.com',
            'user_id' => 2, // Assuming user with ID 2 exists
            'subscription_id' => 1, // No subscription initially
            'product_id' => 1, // No product initially
            'shopify_api_key' => 'demo_api_key_123456',
            'shopify_api_secret' => 'demo_api_secret_123456',
        ]);
    }
}
