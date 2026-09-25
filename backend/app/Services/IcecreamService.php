<?php

namespace App\Services;

use App\Models\Icecream;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class IcecreamService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function list(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = Icecream::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
                $q->orWhere('flavor', 'like', "%{$search}%");
            });
        }

        $query->latest();

        if (! empty($filters['per_page'])) {
            return $query->paginate((int) $filters['per_page']);
        }

        return $query->get();
    }

    public function find(int $id): Icecream
    {
        return Icecream::query()->findOrFail($id);
    }

    public function create(array $data): Icecream
    {
        $record = Icecream::query()->create($data);

        $this->auditLogService->logRecordCreatedIfEnabled($record);

        return $record;
    }

    public function update(Icecream $icecream, array $data): Icecream
    {
        $oldValues = $icecream->attributesToArray();

        $icecream->update($data);

        $record = $icecream->fresh();

        $this->auditLogService->logRecordUpdatedIfEnabled(
            $record,
            $oldValues,
            $record->attributesToArray()
        );

        return $record;
    }

    public function delete(Icecream $icecream): bool
    {
        $this->auditLogService->logRecordDeletedIfEnabled($icecream);

        return (bool) $icecream->delete();
    }
}
