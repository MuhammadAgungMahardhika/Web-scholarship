<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    /** @use HasFactory<\Database\Factories\DepartmentFactory> */
    use HasFactory;
    use Blameable;

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
