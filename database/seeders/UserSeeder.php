<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Demo',
            'email' => 'admin@test.com',
            'password' => Hash::make('12345678'), // password: "12345678"
        ]);

        User::create([
            'name' => 'User Demo',
            'email' => 'user@test.com',
            'password' => Hash::make('12345678'), // password: "12345678"
        ]);
    }
}
