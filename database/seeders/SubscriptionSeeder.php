<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subscription::factory()->create([
            'user_id' => 2, // Assuming user with ID 2 exists
            'product_id' => 1, // Assuming product with ID 1 exists
            'type' => 'basic',
            'stripe_id' => 'sub_1234567890',
            'stripe_status' => 'active',
            'stripe_price' => 9.99,
            'quantity' => 1,
            'trial_ends_at' => null,
            'start_at' => now(),
            'ends_at' => now()->addMonth(1),
        ]);
    }
}
