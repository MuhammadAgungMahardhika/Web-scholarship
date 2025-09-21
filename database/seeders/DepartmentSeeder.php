<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('database/csv/departmen.csv');

        // Membuka file CSV
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Membaca header (baris pertama) dan mengabaikannya
            $header = fgetcsv($handle, 1000, ';'); // Menyesuaikan dengan delimiter ";"

            // Membaca setiap baris CSV dan memasukkan data
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                DB::table('departments')->insert([
                    'faculty_id' => $data[1], // Mengambil id_fakultas dari CSV (data[1] karena data pertama adalah 'id')
                    'name' => $data[2],
                ]);
            }

            // Menutup file setelah selesai membaca
            fclose($handle);
        }
    }
}
