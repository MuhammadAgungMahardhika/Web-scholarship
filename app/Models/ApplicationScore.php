<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationScore extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationScoreFactory> */
    use HasFactory;
    use Blameable;


    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function criteria()
    {
        return $this->belongsTo(Criteria::class);
    }
}
