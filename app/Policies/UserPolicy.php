<?php

namespace App\Policies;

use App\Models\User;
use App\Traits\HasPolicyAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HasPolicyAuthorization;
}
