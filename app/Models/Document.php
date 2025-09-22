<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;
    use Blameable;

    public function applicationData()
    {
        return $this->belongsTo(ApplicationData::class);
    }

    public function application()
    {
        return $this->hasOneThrough(Application::class, ApplicationData::class, 'id', 'id', 'application_data_id', 'application_id');
    }
}
