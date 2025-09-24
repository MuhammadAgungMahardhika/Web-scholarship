<?php

namespace App\Policies;

use App\Models\Faculty;
use App\Models\User;
use App\Traits\HasPolicyAuthorization;
use Illuminate\Auth\Access\Response;

class FacultyPolicy
{
    use HasPolicyAuthorization;
}
