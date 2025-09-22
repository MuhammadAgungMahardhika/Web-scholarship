<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationScore;
use App\Models\Enums\ApplicationStatusEnum;
use InvalidArgumentException;

class ApplicationService
{
    public function updateApplicationStatus(Application $record, ApplicationStatusEnum $newStatus): bool
    {
        if ($newStatus === ApplicationStatusEnum::RequestVerify) {
            $record->status = $newStatus->value;
            $isSuccessed =   $record->save();
        }
        if ($newStatus === ApplicationStatusEnum::Verified) {
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
