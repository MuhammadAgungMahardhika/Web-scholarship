<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AhpCalculatorService
{
    /**
     * Tabel Rasio Indeks (RI) standar untuk perhitungan konsistensi.
     * Kunci adalah jumlah kriteria (n).
     */
    private const RANDOM_INDEX = [
        1 => 0,
        2 => 0,
        3 => 0.58,
        4 => 0.90,
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49,
    ];


    /**
     * Menerima matriks perbandingan dan menghitung bobot kriteria
     * beserta rasio konsistensinya.
     *
     * @param array $matrix Matriks perbandingan berpasangan.
     * @return array Hasil perhitungan berisi 'weights', 'consistency_ratio', dan 'is_consistent'.
     */
    public function calculate(array $matrix): array
    {
        $n = count($matrix);
        if ($n === 0) {
            return [
                'weights' => [],
                'consistency_ratio' => 0,
                'is_consistent' => true,
                'error' => 'Matrix is empty.'
            ];
        }

        // Debug: Log original matrix
        Log::info('AHP Original Matrix', ['matrix' => $matrix, 'size' => $n]);

        // === Langkah 1: Normalisasi Matriks ===
        $columnSums = array_fill(0, $n, 0);
        for ($j = 0; $j < $n; $j++) {
            for ($i = 0; $i < $n; $i++) {
                $columnSums[$j] += $matrix[$i][$j];
            }
        }

        // Debug: Log column sums
        Log::info('AHP Column Sums', $columnSums);

        $normalizedMatrix = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $normalizedMatrix[$i][$j] = $columnSums[$j] > 0 ? $matrix[$i][$j] / $columnSums[$j] : 0;
            }
        }

        // Debug: Log normalized matrix
        Log::info('AHP Normalized Matrix', $normalizedMatrix);

        // === Langkah 2: Hitung Vektor Bobot ===
        $weights = [];
        for ($i = 0; $i < $n; $i++) {
            $weights[$i] = array_sum($normalizedMatrix[$i]) / $n;
        }

        // Debug: Log raw weights and total
        $totalWeight = array_sum($weights);
        Log::info('AHP Raw Weights', [
            'weights' => $weights,
            'total' => $totalWeight,
            'expected_individual' => 1 / $n,
            'all_equal_check' => array_map(fn($w) => abs($w - (1 / $n)) < 0.000001, $weights)
        ]);

        // === Langkah 3: Normalisasi Final (memastikan total = 1) ===
        if ($totalWeight > 0) {
            for ($i = 0; $i < $n; $i++) {
                $weights[$i] = $weights[$i] / $totalWeight;
            }
        }

        // Debug: Log final weights
        Log::info('AHP Final Normalized Weights', [
            'weights' => $weights,
            'total' => array_sum($weights),
            'precision_check' => array_map(fn($w) => number_format($w, 10), $weights)
        ]);

        // === Langkah 4: Hitung Rasio Konsistensi ===
        $weightedSumVector = [];
        for ($i = 0; $i < $n; $i++) {
            $sum = 0;
            for ($j = 0; $j < $n; $j++) {
                $sum += $matrix[$i][$j] * $weights[$j];
            }
            $weightedSumVector[$i] = $sum;
        }

        $lambdaMax = 0;
        for ($i = 0; $i < $n; $i++) {
            $lambdaMax += $weights[$i] > 0 ? $weightedSumVector[$i] / $weights[$i] : 0;
        }
        $lambdaMax /= $n;

        $consistencyIndex = ($n > 1) ? ($lambdaMax - $n) / ($n - 1) : 0;
        $randomIndex = self::RANDOM_INDEX[$n] ?? 1.49;
        $consistencyRatio = ($randomIndex > 0) ? $consistencyIndex / $randomIndex : 0;

        return [
            'weights'           => $weights,
            'consistency_ratio' => $consistencyRatio,
            'is_consistent'     => $consistencyRatio < 0.1,
            'debug_info' => [
                'lambda_max' => $lambdaMax,
                'consistency_index' => $consistencyIndex,
                'random_index' => $randomIndex,
                'total_weight' => array_sum($weights)
            ]
        ];
    }
}
