<?php

namespace App\Policies;

use App\Models\City;
use App\Models\User;
use App\Traits\HasPolicyAuthorization;
use Illuminate\Auth\Access\Response;

class CityPolicy
{
    use HasPolicyAuthorization;
}
