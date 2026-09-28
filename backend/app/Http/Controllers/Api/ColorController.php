<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Color\StoreColorRequest;
use App\Http\Requests\Color\UpdateColorRequest;
use App\Http\Resources\ColorResource;
use App\Services\ColorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ColorController extends Controller
{
    public function __construct(
        protected ColorService $service
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $items = $this->service->list($request->all());

        return ColorResource::collection($items);
    }

    public function store(StoreColorRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return (new ColorResource($item))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): ColorResource
    {
        return new ColorResource($this->service->find($id));
    }

    public function update(UpdateColorRequest $request, int $id): ColorResource
    {
        $item = $this->service->update($this->service->find($id), $request->validated());

        return new ColorResource($item);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($this->service->find($id));

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
