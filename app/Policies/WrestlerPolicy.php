<?php

namespace App\Policies;

use App\User;
use App\Wrestler;
use Illuminate\Auth\Access\HandlesAuthorization;

class WrestlerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create wrestlers.
     *
     * @param  \App\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can update a wrestler.
     *
     * @param  \App\User  $user
     * @return bool
     */
    public function update(User $user, Wrestler $wrestler)
    {
        return $user->isAdministrator();
    }
}
