<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriteriaRequiredDocument extends Model
{
    /** @use HasFactory<\Database\Factories\CriteriaRequiredDocumentFactory> */
    use HasFactory;
    use Blameable;

    public function criteria()
    {
        return $this->belongsTo(Criteria::class);
    }
}
