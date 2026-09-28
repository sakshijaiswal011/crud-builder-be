<?php

namespace App\Services;

use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductCategoryService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function list(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = ProductCategory::query();

        $searchableFields = ['category_name'];

        if (! empty($filters['search']) && is_array($filters['search'])) {
            foreach ($filters['search'] as $field => $term) {
                if (! is_string($field) || ! in_array($field, $searchableFields, true)) {
                    continue;
                }
                $term = is_scalar($term) ? trim((string) $term) : '';
                if ($term === '') {
                    continue;
                }
                $query->where($field, 'like', '%'.$term.'%');
            }
        }

        $sortableFields = ['category_name', 'status'];
        $sortBy = $filters['sort_by'] ?? null;
        $sortDir = isset($filters['sort_dir']) && strtolower((string) $filters['sort_dir']) === 'desc' ? 'desc' : 'asc';

        if (is_string($sortBy) && in_array($sortBy, $sortableFields, true)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest();
        }

        if (! empty($filters['per_page'])) {
            return $query->paginate((int) $filters['per_page']);
        }

        return $query->get();
    }

    public function find(int $id): ProductCategory
    {
        return ProductCategory::query()->findOrFail($id);
    }

    public function create(array $data): ProductCategory
    {
        $record = ProductCategory::query()->create($data);

        $this->auditLogService->logRecordCreatedIfEnabled($record);

        return $record;
    }

    public function update(ProductCategory $productCategory, array $data): ProductCategory
    {
        $oldValues = $productCategory->attributesToArray();

        $productCategory->update($data);

        $record = $productCategory->fresh();

        $this->auditLogService->logRecordUpdatedIfEnabled(
            $record,
            $oldValues,
            $record->attributesToArray()
        );

        return $record;
    }

    public function delete(ProductCategory $productCategory): bool
    {
        $this->auditLogService->logRecordDeletedIfEnabled($productCategory);

        return (bool) $productCategory->delete();
    }
}
