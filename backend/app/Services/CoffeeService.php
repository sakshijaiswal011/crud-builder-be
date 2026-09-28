<?php

namespace App\Services;

use App\Models\Coffee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CoffeeService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function list(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = Coffee::query();

        $query->with(['color']);

        $searchableFields = ['name', 'desc', 'description'];

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

        $sortableFields = ['code', 'color_id', 'description'];
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

    public function find(int $id): Coffee
    {
        return Coffee::query()->findOrFail($id);
    }

    public function create(array $data): Coffee
    {
        $record = Coffee::query()->create($data);

        $this->auditLogService->logRecordCreatedIfEnabled($record);

        return $record;
    }

    public function update(Coffee $coffee, array $data): Coffee
    {
        $oldValues = $coffee->attributesToArray();

        $coffee->update($data);

        $record = $coffee->fresh();

        $this->auditLogService->logRecordUpdatedIfEnabled(
            $record,
            $oldValues,
            $record->attributesToArray()
        );

        return $record;
    }

    public function delete(Coffee $coffee): bool
    {
        $this->auditLogService->logRecordDeletedIfEnabled($coffee);

        return (bool) $coffee->delete();
    }
}
