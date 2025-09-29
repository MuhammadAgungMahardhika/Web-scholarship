<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\Http; // <-- Ganti Process dengan Http
use Illuminate\Support\Facades\Log;

class PredictionService
{
    public function getPrediction(Application $application): ?array
    {
        if (!$application->student || is_null($application->final_score)) {
            return null;
        }

        $features = [
            'gpa' => $application->student->gpa,
            'parent_income' => $application->student->parent_income,
            'final_score' => $application->final_score,
        ];

        // Kirim request POST ke API Python lokal kita
        $response = Http::timeout(5)->post('http://127.0.0.1:5000/predict', $features);

        // Cek jika request berhasil dan kembalikan hasilnya
        if ($response->successful()) {
            return $response->json();
        }

        // Jika gagal, catat error (opsional)
        Log::error('Failed to connect to Python API: ' . $response->body());
        return null;
    }
}
