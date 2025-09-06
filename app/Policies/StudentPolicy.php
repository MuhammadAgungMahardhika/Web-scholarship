<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Traits\HasPolicyAuthorization;
use Illuminate\Auth\Access\Response;

class StudentPolicy
{
    use HasPolicyAuthorization;
}
