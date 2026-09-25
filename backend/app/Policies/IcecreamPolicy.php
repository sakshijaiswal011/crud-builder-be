<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Icecream;

class IcecreamPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Icecream $icecream): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Icecream $icecream): bool
    {
        return true;
    }

    public function delete(User $user, Icecream $icecream): bool
    {
        return true;
    }
}
