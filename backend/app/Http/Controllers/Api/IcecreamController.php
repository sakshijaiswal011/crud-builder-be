<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Icecream\StoreIcecreamRequest;
use App\Http\Requests\Icecream\UpdateIcecreamRequest;
use App\Http\Resources\IcecreamResource;
use App\Services\IcecreamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IcecreamController extends Controller
{
    public function __construct(
        protected IcecreamService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $items = $this->service->list($request->all());

        return IcecreamResource::collection($items);
    }

    public function store(StoreIcecreamRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return (new IcecreamResource($item))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): IcecreamResource
    {
        return new IcecreamResource($this->service->find($id));
    }

    public function update(UpdateIcecreamRequest $request, int $id): IcecreamResource
    {
        $item = $this->service->update($this->service->find($id), $request->validated());

        return new IcecreamResource($item);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($this->service->find($id));

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
