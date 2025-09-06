<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationFactory> */
    use HasFactory;
    use Blameable;

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
    public function applicationScores()
    {
        return $this->hasMany(ApplicationScore::class);
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
