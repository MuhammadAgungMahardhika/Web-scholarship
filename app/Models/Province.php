<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    /** @use HasFactory<\Database\Factories\ProvinceFactory> */
    use HasFactory;
    use Blameable;

    public function students()
    {
        return $this->hasMany(Student::class);
    }
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
