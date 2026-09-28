<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Country;

class CountryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('country.view');
    }

    public function view(User $user, Country $country): bool
    {
        return $user->can('country.view');
    }

    public function create(User $user): bool
    {
        return $user->can('country.create');
    }

    public function update(User $user, Country $country): bool
    {
        return $user->can('country.update');
    }

    public function delete(User $user, Country $country): bool
    {
        return $user->can('country.delete');
    }

    public function export(User $user): bool
    {
        return $user->can('country.export');
    }
}
