<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Country\StoreCountryRequest;
use App\Http\Requests\Country\UpdateCountryRequest;
use App\Http\Resources\CountryResource;
use App\Services\CountryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CountryController extends Controller
{
    public function __construct(
        protected CountryService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $items = $this->service->list($request->all());

        return CountryResource::collection($items);
    }

    public function store(StoreCountryRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return (new CountryResource($item))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): CountryResource
    {
        return new CountryResource($this->service->find($id));
    }

    public function update(UpdateCountryRequest $request, int $id): CountryResource
    {
        $item = $this->service->update($this->service->find($id), $request->validated());

        return new CountryResource($item);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($this->service->find($id));

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
