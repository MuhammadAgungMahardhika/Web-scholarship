<?php

namespace App\Policies;

use App\Models\Scholarship;
use App\Models\User;
use App\Traits\HasPolicyAuthorization;
use Illuminate\Auth\Access\Response;

class ScholarshipPolicy
{
    use HasPolicyAuthorization;
}
