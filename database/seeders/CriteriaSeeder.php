<?php

namespace Database\Seeders;

use App\Models\Criteria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data kriteria dan skala penilaiannya berdasarkan catatan klien
        $data = [
            [
                'name' => 'Status',
                'scales' => [
                    ['value' => 'Reguler', 'score' => 20],
                    ['value' => 'Mandiri', 'score' => 10],
                ]
            ],
            [
                'name' => 'Status Hidup Orang Tua',
                'scales' => [
                    ['value' => 'Keduanya hidup', 'score' => 10],
                    ['value' => 'Meninggal salah satu', 'score' => 18],
                    ['value' => 'Meninggal keduanya', 'score' => 25],
                ]
            ],
            [
                'name' => 'Beban Keluarga',
                'scales' => [
                    ['value' => '> 2000000', 'score' => 0],
                    ['value' => '1600001-2000000', 'score' => 4],
                    ['value' => '1200001-1600000', 'score' => 8],
                    ['value' => '800001-1200000', 'score' => 12],
                    ['value' => '400001-800000', 'score' => 16],
                    ['value' => '<= 400000', 'score' => 20],
                ]
            ],
            [
                'name' => 'IPK',
                'scales' => [
                    ['value' => '> 3.0', 'score' => 5],
                    ['value' => '2.51-3.0', 'score' => 3],
                    ['value' => '<= 2.5', 'score' => 1],
                ]
            ],
            [
                'name' => 'Lokasi Rumah',
                'scales' => [
                    ['value' => 'Padang', 'score' => 3],
                    ['value' => 'Luar Padang', 'score' => 5],
                ]
            ],
            [
                'name' => 'Kondisi Rumah',
                'scales' => [
                    ['value' => 'Mewah', 'score' => 2],
                    ['value' => 'Layak', 'score' => 6],
                    ['value' => 'Tidak layak', 'score' => 10],
                ]
            ],
            [
                'name' => 'UKT',
                'scales' => [
                    ['value' => '> 6000000', 'score' => 10],
                    ['value' => '5000001-6000000', 'score' => 8],
                    ['value' => '4000001-5000000', 'score' => 6],
                    ['value' => '3000001-4000000', 'score' => 4],
                    ['value' => '<= 3000000', 'score' => 2],
                ]
            ],
            [
                'name' => 'Listrik',
                'scales' => [
                    ['value' => '> 400000', 'score' => 1],
                    ['value' => '200001-400000', 'score' => 2],
                    ['value' => '100001-200000', 'score' => 3],
                    ['value' => '<= 100000', 'score' => 5],
                ]
            ],
        ];

        // Loop untuk memasukkan data ke database
        foreach ($data as $item) {
            // 1. Buat Kriteria baru
            $criteria = Criteria::create([
                'name' => $item['name'],
                // Anda bisa menambahkan 'data_type' di sini jika perlu
                'data_type' => 'select',
            ]);

            // 2. Buat Skala Penilaian yang berelasi dengan kriteria di atas
            if (isset($item['scales'])) {
                $criteria->scoringScales()->createMany($item['scales']);
            }
        }
    }
}
