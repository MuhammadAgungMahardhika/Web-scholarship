<?php

namespace App\Services;

use App\Models\Application;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PredictionService
{
    public function getPrediction(Application $application): ?array
    {
        // Pastikan data yang dibutuhkan ada
        if (!$application->student || is_null($application->final_score)) {
            return null;
        }

        $modelPath = storage_path('app/ml/scholarship_model.joblib');
        // Hentikan jika model belum dilatih
        if (!file_exists($modelPath)) {
            return null;
        }

        $scriptPath = storage_path('app/ml/predict.py');
        $features = [
            'gpa' => $application->student->gpa,
            'parent_income' => $application->student->parent_income,
            'final_score' => $application->final_score,
        ];

        $process = new Process(['python', $scriptPath, json_encode($features)]);

        try {
            $process->mustRun();

            return json_decode($process->getOutput(), true);
        } catch (ProcessFailedException $exception) {
            // Untuk debugging, Anda bisa log error di sini
            // \Log::error($exception->getMessage());
            dd($exception);
            return null;
        }
    }
}
