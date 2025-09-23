<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationScore;
use App\Models\Enums\ApplicationStatusEnum;
use App\Models\Enums\DocumentStatusEnum;
use Exception;
use InvalidArgumentException;

class ApplicationService
{
    public function updateApplicationStatus(Application $record, ApplicationStatusEnum $newStatus): bool
    {
        if ($newStatus === ApplicationStatusEnum::RequestVerify) {
            $hasIncompleteData = $record->applicationData->contains(function ($appData) {
                // Check if value is empty
                if (empty($appData->value)) {
                    return true;
                }
                // Check if documents are empty or any document missing file
                return $appData->documents->isEmpty() ||
                    $appData->documents->contains(fn($doc) => empty($doc->file_path));
            });

            if ($hasIncompleteData) {
                throw new Exception('Aplikasi tidak dapat diverifikasi: Ada data atau dokumen yang belum lengkap');
            }
            $record->status = $newStatus->value;
            $isSuccessed =   $record->save();
        }
        if ($newStatus === ApplicationStatusEnum::Verified) {
            // 1. Cek apakah ada data aplikasi yang nilainya kosong
            $hasIncompleteData = $record->applicationData()->whereNull('value')->orWhere('value', '')->exists();

            // 2. Cek apakah ada dokumen yang statusnya belum terverifikasi
            $hasUnverifiedDocuments = $record->documents()->where('documents.status', '!=', DocumentStatusEnum::Verified->value)->exists();
            if ($hasIncompleteData ||  $hasUnverifiedDocuments) {
                throw new Exception('Data & dokumen ada yg belum valid');
            }
            $record->status = $newStatus->value;
            $isSuccessed =   $record->save();
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
                    'file_path' => null,
                    'status' => 1,
                ]);
            }
        }

        return $application;
    }
}
