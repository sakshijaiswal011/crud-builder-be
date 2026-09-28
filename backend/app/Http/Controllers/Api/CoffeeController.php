<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coffee\StoreCoffeeRequest;
use App\Http\Requests\Coffee\UpdateCoffeeRequest;
use App\Http\Resources\CoffeeResource;
use App\Services\CoffeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CoffeeController extends Controller
{
    public function __construct(
        protected CoffeeService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $items = $this->service->list($request->all());

        return CoffeeResource::collection($items);
    }

    public function store(StoreCoffeeRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return (new CoffeeResource($item))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): CoffeeResource
    {
        return new CoffeeResource($this->service->find($id));
    }

    public function update(UpdateCoffeeRequest $request, int $id): CoffeeResource
    {
        $item = $this->service->update($this->service->find($id), $request->validated());

        return new CoffeeResource($item);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($this->service->find($id));

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
