<?php

namespace App\Policies;

use App\Models\Province;
use App\Models\User;
use App\Traits\HasPolicyAuthorization;
use Illuminate\Auth\Access\Response;

class ProvincePolicy
{
    use HasPolicyAuthorization;
}
