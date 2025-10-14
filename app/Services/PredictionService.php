<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Http\Client\ConnectionException;
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
        try { // <-- PERUBAHAN: Bungkus dengan try...catch
            $response = Http::timeout(5)->post('http://127.0.0.1:5000/predict', $features);

            if ($response->successful()) {
                return $response->json();
            }

            // Jika response tidak sukses, catat errornya
            Log::error('Python API returned an error: ', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (ConnectionException $e) {
            // <-- PERUBAHAN: Tangani jika server Python mati
            Log::error('Failed to connect to Python API: ' . $e->getMessage());
            return null;
        }
    }
}
