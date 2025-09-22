<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Criteria extends Model
{
    /** @use HasFactory<\Database\Factories\CriteriaFactory> */
    use HasFactory;
    use Blameable;

    public function criteriaRequiredDocuments()
    {
        return $this->hasMany(CriteriaRequiredDocument::class);
    }
    public function scholarshipCriterias()
    {
        return $this->hasMany(ScholarshipCriteria::class);
    }
    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function scoringScales()
    {
        return $this->hasMany(ScoringScale::class);
    }
}
