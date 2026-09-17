<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Account
        User::updateOrCreate(
            ['email' => 'admin@source-x.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 2. Buyer Account
        User::updateOrCreate(
            ['email' => 'buyer@source-x.test'],
            [
                'name' => 'Buyer User',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'email_verified_at' => now(),
            ]
        );
    }
}
