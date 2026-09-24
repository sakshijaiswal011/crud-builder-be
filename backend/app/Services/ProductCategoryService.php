<?php

namespace App\Services;

use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductCategoryService
{
    public function list(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = ProductCategory::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('category_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['per_page'])) {
            return $query->latest()->paginate((int) $filters['per_page']);
        }

        return $query->latest()->get();
    }

    public function find(int $id): ProductCategory
    {
        return ProductCategory::query()->findOrFail($id);
    }

    public function create(array $data): ProductCategory
    {
        return ProductCategory::query()->create($data);
    }

    public function update(ProductCategory $productCategory, array $data): ProductCategory
    {
        $productCategory->update($data);

        return $productCategory->fresh();
    }

    public function delete(ProductCategory $productCategory): bool
    {
        return (bool) $productCategory->delete();
    }
}
