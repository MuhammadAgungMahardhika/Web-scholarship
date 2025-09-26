<?php

namespace App\Services;

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

        // === Langkah 1: Normalisasi Matriks ===
        $columnSums = array_fill(0, $n, 0);
        for ($j = 0; $j < $n; $j++) {
            for ($i = 0; $i < $n; $i++) {
                $columnSums[$j] += $matrix[$i][$j];
            }
        }

        $normalizedMatrix = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                // Mencegah pembagian dengan nol
                $normalizedMatrix[$i][$j] = $columnSums[$j] > 0 ? $matrix[$i][$j] / $columnSums[$j] : 0;
            }
        }

        // === Langkah 2: Hitung Vektor Bobot (rata-rata setiap baris) ===
        $weights = [];
        for ($i = 0; $i < $n; $i++) {
            $weights[$i] = array_sum($normalizedMatrix[$i]) / $n;
        }

        // === Langkah 3: Hitung Rasio Konsistensi (CR) ===
        // Dot product dari matriks asli dengan vektor bobot
        $weightedSumVector = [];
        for ($i = 0; $i < $n; $i++) {
            $sum = 0;
            for ($j = 0; $j < $n; $j++) {
                $sum += $matrix[$i][$j] * $weights[$j];
            }
            $weightedSumVector[$i] = $sum;
        }

        // Hitung Lambda Max (λmax)
        $lambdaMax = 0;
        for ($i = 0; $i < $n; $i++) {
            // Mencegah pembagian dengan nol jika bobot 0
            $lambdaMax += $weights[$i] > 0 ? $weightedSumVector[$i] / $weights[$i] : 0;
        }
        $lambdaMax /= $n;

        $consistencyIndex = ($n > 1) ? ($lambdaMax - $n) / ($n - 1) : 0;
        $randomIndex = self::RANDOM_INDEX[$n] ?? 1.49; // Default untuk n > 10
        $consistencyRatio = ($randomIndex > 0) ? $consistencyIndex / $randomIndex : 0;

        return [
            'weights'           => $weights,
            'consistency_ratio' => $consistencyRatio,
            'is_consistent'     => $consistencyRatio < 0.1,
        ];
    }
}
