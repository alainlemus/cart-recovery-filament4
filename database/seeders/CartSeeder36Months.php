<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CartSeeder36Months extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shops = Shop::all();

        if ($shops->isEmpty()) {
            $this->command->warn('No shops found. Please seed shops first.');

            return;
        }

        foreach ($shops as $shop) {
            foreach (range(0, 35) as $i) {
                $month = Carbon::now()->subMonths($i);

                // Abandoned cart
                Cart::factory()->create([
                    'shop_id' => $shop->id,
                    'status' => 'abandoned',
                    'recovery_token' => (string) Str::uuid(),
                    'total_price' => fake()->randomFloat(2, 100, 500),
                    'created_at' => $month->copy()->startOfMonth()->addDays(rand(0, 27)),
                    'updated_at' => $month->copy()->startOfMonth()->addDays(rand(0, 27)),
                ]);

                // Recovered via email
                Cart::factory()->create([
                    'shop_id' => $shop->id,
                    'status' => 'complete',
                    'recovered_via' => 'email',
                    'recovery_token' => (string) Str::uuid(),
                    'total_price' => fake()->randomFloat(2, 100, 500),
                    'created_at' => $month->copy()->startOfMonth()->addDays(rand(0, 27)),
                    'updated_at' => $month->copy()->startOfMonth()->addDays(rand(0, 27)),
                ]);
            }
        }

    }
}
