<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    /** @use HasFactory<\Database\Factories\CityFactory> */
    use HasFactory;
    use Blameable;

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
