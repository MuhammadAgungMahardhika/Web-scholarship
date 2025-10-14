<?php

namespace Database\Seeders;

use App\Models\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser =  User::create([
            'name' => 'admin',
            'username' => 'admin', // Username untuk admin
            'email' => 'admin@example.com', // Anda bisa ganti email ini
            'password' => Hash::make('123456'), // Password default yang di-hash
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adminUser->assignRole(RoleEnum::Admin->value);
    }
}
