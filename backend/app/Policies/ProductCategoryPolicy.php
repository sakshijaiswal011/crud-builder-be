<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ProductCategory;

class ProductCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('product-category.view');
    }

    public function view(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('product-category.view');
    }

    public function create(User $user): bool
    {
        return $user->can('product-category.create');
    }

    public function update(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('product-category.update');
    }

    public function delete(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('product-category.delete');
    }

    public function export(User $user): bool
    {
        return $user->can('product-category.export');
    }
}
