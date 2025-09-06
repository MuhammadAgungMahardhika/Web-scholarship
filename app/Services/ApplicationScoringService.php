<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationScore;

class ApplicationScoringService
{
    /**
     * Menghitung skor terbobot untuk setiap kriteria dan menyimpannya.
     *
     * @param Application $application
     * @return void
     */
    public function calculateAndStoreScores(Application $application): void
    {
        $criterias = $application->scholarship->criterias;
        $student = $application->student;
        $finalScore = 0;

        foreach ($criterias as $criteria) {
            $rawScore = 0;

            // Logika penilaian (scoring) berdasarkan nama kriteria
            switch ($criteria->name) {
                case 'Status Mahasiswa':
                    if ($student->status == 'reguler') {
                        $rawScore = 20;
                    } else if ($student->status == 'mandiri') {
                        $rawScore = 10;
                    }
                    break;

                case 'Status Hidup Orang Tua':
                    if ($student->parent_status == 'keduanya hidup') {
                        $rawScore = 10;
                    } else if ($student->parent_status == 'meninggal salah satu') {
                        $rawScore = 18;
                    } else if ($student->parent_status == 'meninggal keduanya') {
                        $rawScore = 25;
                    }
                    break;

                case 'Beban Keluarga':
                    // Asumsi ada kolom parent_income dan family_size
                    $beban = $student->parent_income / $student->family_size;

                    if ($beban > 2000000) {
                        $rawScore = 0;
                    } else if ($beban > 1600000) {
                        $rawScore = 4;
                    } else if ($beban > 1200000) {
                        $rawScore = 8;
                    } else if ($beban > 800000) {
                        $rawScore = 12;
                    } else if ($beban > 400000) {
                        $rawScore = 16;
                    } else { // x <= 400k
                        $rawScore = 20;
                    }
                    break;

                case 'IPK':
                    if ($student->gpa > 3.0) {
                        $rawScore = 5;
                    } else if ($student->gpa > 2.5) {
                        $rawScore = 3;
                    } else { // x <= 2.5
                        $rawScore = 1;
                    }
                    break;

                case 'Lokasi Rumah':
                    if ($student->address_location == 'Kota Padang') {
                        $rawScore = 3;
                    } else if ($student->address_location == 'Luar Padang') {
                        $rawScore = 5;
                    }
                    break;

                case 'Kondisi Rumah':
                    if ($student->house_condition == 'mewah') {
                        $rawScore = 2;
                    } else if ($student->house_condition == 'layak') {
                        $rawScore = 6;
                    } else if ($student->house_condition == 'tidak layak') {
                        $rawScore = 10;
                    }
                    break;

                case 'UKT':
                    if ($student->ukt > 6000000) {
                        $rawScore = 10;
                    } else if ($student->ukt > 5000000) {
                        $rawScore = 8;
                    } else if ($student->ukt > 4000000) {
                        $rawScore = 6;
                    } else if ($student->ukt > 3000000) {
                        $rawScore = 4;
                    } else { // x <= 3000k
                        $rawScore = 2;
                    }
                    break;

                default:
                    $rawScore = 0;
                    break;
            }

            // Normalisasi skor mentah ke skala 0-1
            // Dapatkan skor maksimum untuk kriteria ini
            $maxScore = 25; // Asumsi skor tertinggi yang mungkin adalah 25 (dari 'meninggal keduanya')
            $normalizedScore = $rawScore / $maxScore;

            // Hitung skor akhir dengan bobot AHP
            $weightedScore = $normalizedScore * $criteria->weight;

            // Simpan skor terbobot ke tabel application_scores
            ApplicationScore::create([
                'application_id' => $application->id,
                'criteria_id' => $criteria->id,
                'score' => $weightedScore,
            ]);

            // Tambahkan ke total skor akhir
            $finalScore += $weightedScore;
        }

        $application->final_score = $finalScore;
        $application->save();
    }
}
