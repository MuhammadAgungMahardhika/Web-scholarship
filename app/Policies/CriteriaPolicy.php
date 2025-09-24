<?php

namespace App\Policies;

use App\Models\Criteria;
use App\Models\User;
use App\Traits\HasPolicyAuthorization;
use Illuminate\Auth\Access\Response;

class CriteriaPolicy
{
    use HasPolicyAuthorization;
}
