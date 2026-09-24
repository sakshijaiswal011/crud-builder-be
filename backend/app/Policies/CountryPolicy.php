<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Country;

class CountryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Country $country): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Country $country): bool
    {
        return true;
    }

    public function delete(User $user, Country $country): bool
    {
        return true;
    }
}
