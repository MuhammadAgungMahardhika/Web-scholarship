<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    /** @use HasFactory<\Database\Factories\ScholarshipFactory> */
    use HasFactory;
    use Blameable;

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    public function scolarshipCriterias()
    {
        return $this->hasMany(ScholarshipCriteria::class);
    }
}
