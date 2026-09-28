<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Color;

class ColorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('color.view');
    }

    public function view(User $user, Color $color): bool
    {
        return $user->can('color.view');
    }

    public function create(User $user): bool
    {
        return $user->can('color.create');
    }

    public function update(User $user, Color $color): bool
    {
        return $user->can('color.update');
    }

    public function delete(User $user, Color $color): bool
    {
        return $user->can('color.delete');
    }

    public function export(User $user): bool
    {
        return $user->can('color.export');
    }
}
