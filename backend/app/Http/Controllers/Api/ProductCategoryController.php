<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategory\StoreProductCategoryRequest;
use App\Http\Requests\ProductCategory\UpdateProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Services\ProductCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductCategoryController extends Controller
{
    public function __construct(
        protected ProductCategoryService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $items = $this->service->list($request->all());

        return ProductCategoryResource::collection($items);
    }

    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return (new ProductCategoryResource($item))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): ProductCategoryResource
    {
        return new ProductCategoryResource($this->service->find($id));
    }

    public function update(UpdateProductCategoryRequest $request, int $id): ProductCategoryResource
    {
        $item = $this->service->update($this->service->find($id), $request->validated());

        return new ProductCategoryResource($item);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($this->service->find($id));

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
