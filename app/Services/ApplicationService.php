<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationData;
use App\Models\ApplicationScore;
use App\Models\Enums\ApplicationDataStatusEnum;
use App\Models\Enums\ApplicationStatusEnum;
use App\Models\Enums\DocumentStatusEnum;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\FacadesLog;
use InvalidArgumentException;

class ApplicationService
{
    public function updateApplicationStatus(Application $record, ApplicationStatusEnum $newStatus): bool
    {

        if ($newStatus === ApplicationStatusEnum::RequestVerify) {
            $incompleteItems = [];

            foreach ($record->applicationData as $appData) {
                $criteriaName = $appData->criteria->name ?? 'Unknown Criteria';

                // Check if value is empty
                if (empty($appData->value)) {
                    $incompleteItems[] = "Data '{$criteriaName}' belum diisi";
                    continue;
                }

                // Check documents only if they exist
                if ($appData->documents->isNotEmpty()) {
                    $missingDocuments = $appData->documents->filter(fn($doc) => empty($doc->file_path));

                    if ($missingDocuments->isNotEmpty()) {
                        $docNames = $missingDocuments->pluck('name')->join(', ');
                        $incompleteItems[] = "Dokumen '{$docNames}' pada kriteria '{$criteriaName}' belum diupload";
                    }
                }
                // If no documents exist, skip document validation for this criteria
            }

            if (!empty($incompleteItems)) {
                $errorMessage = "Aplikasi tidak dapat diverifikasi:\n" . implode("\n", $incompleteItems);
                throw new Exception($errorMessage);
            }

            $record->status = $newStatus->value;
            $isSuccessed = $record->save();
        }
        if ($newStatus === ApplicationStatusEnum::Verified) {
            // 1. Cek apakah ada data aplikasi yang nilainya kosong
            $hasIncompleteData = $record->applicationData()->whereNull('value')->orWhere('value', '')->exists();

            // 2. Cek apakah ada dokumen yang statusnya belum terverifikasi
            $hasUnverifiedDocuments = $record->applicationData()->where('status', '!=', ApplicationDataStatusEnum::Verified->value)->exists();
            if ($hasIncompleteData ||  $hasUnverifiedDocuments) {
                throw new Exception('Data & dokumen ada yg belum valid');
            }
            $record->status = $newStatus->value;
            $isSuccessed =   $record->save();

            // 3. Generate Application Score setelah berhasil verified
            if ($isSuccessed) {
                $this->generateApplicationScore($record);
            }
        }
        if ($newStatus === ApplicationStatusEnum::Rejected) {
            $record->status = $newStatus->value;
            $isSuccessed =   $record->save();
        }
        return $isSuccessed;
    }
    public function generateApplicationData(Application $application): Application
    {
        $criteriaList = $application->scholarship->scholarshipCriterias;

        foreach ($criteriaList as $scholarshipCriteria) {
            $applicationData = $application->applicationData()->create([
                'criteria_id' => $scholarshipCriteria->criteria_id,
                'value' => null,
            ]);

            // Handle multiple required documents per criteria
            $requiredDocuments = $scholarshipCriteria->criteria->criteriaRequiredDocuments;

            foreach ($requiredDocuments as $requiredDocument) {
                $applicationData->documents()->create([
                    'name' => $requiredDocument->name,
                    'is_required' => $requiredDocument->is_required,
                    'file_path' => null,
                    'status' => 1,
                ]);
            }
        }

        return $application;
    }

    /**
     * Generate application score berdasarkan application_data.value dan scoring_scales.value
     */
    private function generateApplicationScore($application)
    {
        try {
            // Hapus score lama jika ada (untuk recalculation)
            ApplicationScore::where('application_id', $application->id)->delete();

            // Ambil semua application data dengan criteria dan scoring scales
            $applicationData = ApplicationData::where('application_id', $application->id)
                ->with(['criteria.scoringScales'])
                ->get();

            $totalScore = 0;
            $scoreDetails = [];

            foreach ($applicationData as $appData) {
                $criteria = $appData->criteria;
                $inputValue = $appData->value; // Nilai yang diinput user

                // Cari score dari scoring_scales berdasarkan value yang match dengan input
                $scoringScale = $criteria->scoringScales()
                    ->where('value', $inputValue)
                    ->first();

                $score = 0;

                if ($scoringScale) {
                    $score = (float) $scoringScale->score;
                } else {
                    // Jika tidak ada exact match, coba cari berdasarkan range atau pattern
                    $score = $this->findScoreByValue($criteria, $inputValue);
                }

                // Ambil weight dari pivot table scholarship_criterias
                $scholarshipCriteria = $application->scholarship->criterias()
                    ->where('criteria_id', $criteria->id)
                    ->first();
                $weight = $scholarshipCriteria ? (float) $scholarshipCriteria->pivot->weight : 0;
                // Hitung weighted score
                $weightedScore = $score * $weight;
                $totalScore += $weightedScore;
                // Simpan ke application_scores
                ApplicationScore::create([
                    'application_id' => $application->id,
                    'criteria_id' => $criteria->id,
                    'score' => $score,
                    'weight' => $weight,
                    'weighted_score' => $weightedScore,
                    'created_by' => Auth::user()?->name ?? 'System',
                ]);

                $scoreDetails[] = [
                    'criteria' => $criteria->name,
                    'input_value' => $inputValue,
                    'matched_scale' => $scoringScale?->value ?? 'Manual Calculation',
                    'score' => $score
                ];
            }

            // Update total score di application (jika ada kolom final_score)
            $application->update(['final_score' => $totalScore]);

            // Log untuk debugging
            Log::info('Application Score Generated', [
                'application_id' => $application->id,
                'final_score' => $totalScore,
                'details' => $scoreDetails
            ]);
            Notification::make()
                ->title('Score Berhasil Dihitung')
                ->body("Total Score: " . number_format($totalScore, 2) . " dari " . count($scoreDetails) . " kriteria")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('Failed to generate application score', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);

            Notification::make()
                ->title('Gagal Menghitung Score')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }

    /**
     * Cari score berdasarkan value jika tidak ada exact match di scoring_scales
     */
    private function findScoreByValue($criteria, $inputValue): int
    {
        $scoringScales = $criteria->scoringScales()->orderBy('score', 'desc')->get();

        // Jika input adalah angka, coba cari range yang sesuai
        if (is_numeric($inputValue)) {
            $numericValue = (float) $inputValue;

            foreach ($scoringScales as $scale) {
                // Cek jika value berisi range (contoh: "80-100", ">= 80", "< 60")
                if (preg_match('/(\d+)-(\d+)/', $scale->value, $matches)) {
                    $min = (float) $matches[1];
                    $max = (float) $matches[2];
                    if ($numericValue >= $min && $numericValue <= $max) {
                        return $scale->score;
                    }
                }

                // Cek operator >= 
                if (preg_match('/>= (\d+)/', $scale->value, $matches)) {
                    $threshold = (float) $matches[1];
                    if ($numericValue >= $threshold) {
                        return $scale->score;
                    }
                }

                // Cek operator >
                if (preg_match('/> (\d+)/', $scale->value, $matches)) {
                    $threshold = (float) $matches[1];
                    if ($numericValue > $threshold) {
                        return $scale->score;
                    }
                }

                // Cek operator <=
                if (preg_match('/<= (\d+)/', $scale->value, $matches)) {
                    $threshold = (float) $matches[1];
                    if ($numericValue <= $threshold) {
                        return $scale->score;
                    }
                }

                // Cek operator <
                if (preg_match('/< (\d+)/', $scale->value, $matches)) {
                    $threshold = (float) $matches[1];
                    if ($numericValue < $threshold) {
                        return $scale->score;
                    }
                }
            }
        }

        // Jika string, coba case insensitive match
        foreach ($scoringScales as $scale) {
            if (strcasecmp(trim($scale->value), trim($inputValue)) === 0) {
                return $scale->score;
            }
        }

        // Default score jika tidak ada yang match
        return 0;
    }

    /**
     * Recalculate scores untuk application tertentu
     */
    public function recalculateApplicationScore($applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->status !== ApplicationStatusEnum::Verified->value) {
            throw new Exception('Hanya aplikasi dengan status Verified yang dapat dihitung scorenya');
        }

        $this->generateApplicationScore($application);
    }

    /**
     * Batch recalculate untuk semua verified applications dalam scholarship
     */
    public function batchRecalculateScores($scholarshipId)
    {
        $applications = Application::where('scholarship_id', $scholarshipId)
            ->where('status', ApplicationStatusEnum::Verified->value)
            ->get();

        $successCount = 0;
        $errorCount = 0;

        foreach ($applications as $application) {
            try {
                $this->generateApplicationScore($application);
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Failed to calculate score for application {$application->id}: " . $e->getMessage());
            }
        }

        Notification::make()
            ->title('Batch Recalculation Complete')
            ->body("Berhasil: {$successCount}, Gagal: {$errorCount}")
            ->success()
            ->send();
    }
}
