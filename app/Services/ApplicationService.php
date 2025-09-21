<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationScore;

class ApplicationService
{
    public function generateApplicationData(Application $application): Application
    {
        $criteriaList = $application->scholarship->scholarshipCriterias;
        foreach ($criteriaList as $criteria) {
            $application->applicationDatas()->create([
                'criteria_id' => $criteria->id,
                'value' => null,
            ]);
        }
        return $application;
    }
}
