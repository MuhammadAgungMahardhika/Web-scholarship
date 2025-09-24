<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use App\Traits\HasPolicyAuthorization;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    use HasPolicyAuthorization;
}
