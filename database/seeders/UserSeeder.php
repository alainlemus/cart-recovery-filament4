<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@admin.com',
            'role' => 'super-admin',
            'password' => bcrypt('admin123'),
        ]);

        User::factory()->create([
            'name' => 'Admin Shop',
            'email' => 'admin@admin.com',
            'role' => 'admin',
            'password' => bcrypt('admin123'),
        ]);

        User::factory()->create([
            'name' => 'User Shop',
            'email' => 'user@admin.com',
            'role' => 'shop-user',
            'password' => bcrypt('admin123'),
        ]);
    }
}
