<?php

namespace App\Policies;

use App\Models\ApplicationData;
use App\Models\User;
use App\Traits\HasPolicyAuthorization;
use Illuminate\Auth\Access\Response;

class ApplicationDataPolicy
{
    use HasPolicyAuthorization;
}
