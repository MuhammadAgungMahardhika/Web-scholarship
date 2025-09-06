<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path ke file CSV
        $csvFile = database_path('/csv/regencies.csv');

        // Buka file CSV
        if (File::exists($csvFile)) {
            // Baca file CSV
            $csvData = array_map('str_getcsv', file($csvFile));
            foreach ($csvData as $row) {
                DB::table('cities')->insert([
                    'province_id' => $row[1],
                    'name' => $row[2],
                ]);
            }
        }
    }
}
