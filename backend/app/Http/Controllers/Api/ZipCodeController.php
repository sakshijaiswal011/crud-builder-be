<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ZipCode\StoreZipCodeRequest;
use App\Http\Requests\ZipCode\UpdateZipCodeRequest;
use App\Http\Resources\ZipCodeResource;
use App\Services\ZipCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ZipCodeController extends Controller
{
    public function __construct(
        protected ZipCodeService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $items = $this->service->list($request->all());

        return ZipCodeResource::collection($items);
    }

    public function store(StoreZipCodeRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return (new ZipCodeResource($item))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): ZipCodeResource
    {
        return new ZipCodeResource($this->service->find($id));
    }

    public function update(UpdateZipCodeRequest $request, int $id): ZipCodeResource
    {
        $item = $this->service->update($this->service->find($id), $request->validated());

        return new ZipCodeResource($item);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($this->service->find($id));

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
