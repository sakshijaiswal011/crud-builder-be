<?php

namespace App\Services;

use App\Models\Color;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ColorService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function list(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = Color::query();



        $searchableFields = ['name', 'desc', 'code'];

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

        $sortableFields = ['name', 'desc', 'code'];
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

    public function find(int $id): Color
    {
        return Color::query()->findOrFail($id);
    }

    public function create(array $data): Color
    {
        $record = Color::query()->create($data);

        $this->auditLogService->logRecordCreatedIfEnabled($record);

        return $record;
    }

    public function update(Color $color, array $data): Color
    {
        $oldValues = $color->attributesToArray();

        $color->update($data);

        $record = $color->fresh();

        $this->auditLogService->logRecordUpdatedIfEnabled(
            $record,
            $oldValues,
            $record->attributesToArray()
        );

        return $record;
    }

    public function delete(Color $color): bool
    {
        $this->auditLogService->logRecordDeletedIfEnabled($color);

        return (bool) $color->delete();
    }
}
