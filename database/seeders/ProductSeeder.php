<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory()->create([
            'name' => 'Basic Plan',
            'description' => 'Basic subscription plan',
            'price' => 9.99,
            'currency' => 'USD',
            'is_active' => true,
            'features' => ['Individual configuration', 'No setup, or hidden fees', 'Team size: 1 developer', 'Premium support: 6 months', 'Free updates: 6 months'],
            'stripe_product_id' => 'prod_SuE78AmwLg4Jnw',
            'stripe_price_id' => 'price_1RyPfCH0VSO5aAtGeMHorAnf',
        ]);

        Product::factory()->create([
            'name' => 'Standard Plan',
            'description' => 'Standard subscription plan with additional features',
            'price' => 14.99,
            'currency' => 'USD',
            'is_active' => true,
            'features' => ['Individual configuration', 'No setup, or hidden fees', 'Team size: 1 developer', 'Premium support: 6 months', 'Free updates: 6 months'],
            'stripe_product_id' => 'prod_SyzHN2KL8MFVOj',
            'stripe_price_id' => 'price_1S31JHH0VSO5aAtG1H0HRN8w',
        ]);

        Product::factory()->create([
            'name' => 'Premium Plan',
            'description' => 'Premium subscription plan with extra features',
            'price' => 19.99,
            'currency' => 'USD',
            'is_active' => true,
            'features' => ['Individual configuration', 'No setup, or hidden fees', 'Team size: 1 developer', 'Premium support: 6 months', 'Free updates: 6 months'],
            'stripe_product_id' => 'prod_SyzIsHpm6szBHI',
            'stripe_price_id' => 'price_1S31JxH0VSO5aAtGEKqJB0b9',
        ]);
    }
}
