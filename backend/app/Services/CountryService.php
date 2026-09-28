<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CountryService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function list(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = Country::query();

        $searchableFields = ['name', 'short_name', 'code'];

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

        $sortableFields = ['name', 'short_name', 'code'];
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

    public function find(int $id): Country
    {
        return Country::query()->findOrFail($id);
    }

    public function create(array $data): Country
    {
        $record = Country::query()->create($data);

        $this->auditLogService->logRecordCreatedIfEnabled($record);

        return $record;
    }

    public function update(Country $country, array $data): Country
    {
        $oldValues = $country->attributesToArray();

        $country->update($data);

        $record = $country->fresh();

        $this->auditLogService->logRecordUpdatedIfEnabled(
            $record,
            $oldValues,
            $record->attributesToArray()
        );

        return $record;
    }

    public function delete(Country $country): bool
    {
        $this->auditLogService->logRecordDeletedIfEnabled($country);

        return (bool) $country->delete();
    }
}
