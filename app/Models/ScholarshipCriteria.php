<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipCriteria extends Model
{
    /** @use HasFactory<\Database\Factories\ScholarshipCriteriaFactory> */
    use HasFactory;
    use Blameable;
    public $incrementing = true;

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function criteria()
    {
        return $this->belongsTo(Criteria::class);
    }
}
