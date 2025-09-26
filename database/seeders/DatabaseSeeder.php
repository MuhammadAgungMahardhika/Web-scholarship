<?php

namespace Database\Seeders;

use App\Models\Criteria;
use App\Models\Faculty;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $this->call([
            PermissionAndRoleSeeder::class,
            UserSeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,
            FacultySeeder::class,
            DepartmentSeeder::class,
            CriteriaSeeder::class

        ]);
    }
}
