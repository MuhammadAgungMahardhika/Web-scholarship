<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\Enums\ApplicationStatusEnum;
use App\Models\User;
use App\Traits\HasPolicyAuthorization;
use Illuminate\Auth\Access\Response;

class ApplicationPolicy
{
    use HasPolicyAuthorization;

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Application $application): bool
    {
        return  $application->status === ApplicationStatusEnum::Draft->value;
    }
}
