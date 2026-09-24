<?php

namespace App\Services;

use App\Models\ZipCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ZipCodeService
{
    public function list(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = ZipCode::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $query->latest();

        if (! empty($filters['per_page'])) {
            return $query->paginate((int) $filters['per_page']);
        }

        return $query->get();
    }

    public function find(int $id): ZipCode
    {
        return ZipCode::query()->findOrFail($id);
    }

    public function create(array $data): ZipCode
    {
        return ZipCode::query()->create($data);
    }

    public function update(ZipCode $zipCode, array $data): ZipCode
    {
        $zipCode->update($data);

        return $zipCode->fresh();
    }

    public function delete(ZipCode $zipCode): bool
    {
        return (bool) $zipCode->delete();
    }
}
