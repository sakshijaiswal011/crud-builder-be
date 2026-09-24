<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ProductCategory;

class ProductCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ProductCategory $productCategory): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ProductCategory $productCategory): bool
    {
        return true;
    }

    public function delete(User $user, ProductCategory $productCategory): bool
    {
        return true;
    }
}
