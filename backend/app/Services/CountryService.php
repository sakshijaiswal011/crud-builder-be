<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CountryService
{
    public function list(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = Country::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
                $q->orWhere('short_name', 'like', "%{$search}%");
                $q->orWhere('code', 'like', "%{$search}%");
            });
        }

        $query->latest();

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
        return Country::query()->create($data);
    }

    public function update(Country $country, array $data): Country
    {
        $country->update($data);

        return $country->fresh();
    }

    public function delete(Country $country): bool
    {
        return (bool) $country->delete();
    }
}
