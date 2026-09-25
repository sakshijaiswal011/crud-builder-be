<?php

namespace App\Http\Controllers\Api;

use App\Helpers\APIResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CrudModule\CreateCrudModuleRequest;
use App\Models\CrudModule;
use App\Services\Crud\CrudModuleCreatorService;
use Illuminate\Http\JsonResponse;
use \App\Services\Crud\CrudModuleDeleterService;
use Throwable;

class CrudModuleController extends Controller
{
    public function __construct(
        protected CrudModuleCreatorService $creator
    ) {}

    /**
     * Create full CRUD module in one request, then generate application files.
     */
    public function store(CreateCrudModuleRequest $request): JsonResponse
    {
        try {
            $result = $this->creator->create($request->validated());

            return response()->json(
                APIResponseHelper::success(
                    APIResponseHelper::CREATED,
                    'CRUD module created and files generated successfully.',
                    $result
                ),
                APIResponseHelper::CREATED
            );
        } catch (Throwable $e) {
            return response()->json(
                APIResponseHelper::error(
                    APIResponseHelper::SOMETHING_WENT_WRONG,
                    'Failed to create CRUD module.',
                    ['exception' => $e->getMessage()]
                ),
                APIResponseHelper::SOMETHING_WENT_WRONG
            );
        }
    }

    public function show(int $module): JsonResponse
    {
        $crudModule = CrudModule::with([
            'fields',
            'relationships.relatedModule',
            'formLists.field',
            'permissions',
        ])->find($module);

        if (! $crudModule) {
            return response()->json(
                APIResponseHelper::error(APIResponseHelper::NOT_FOUND, 'Module not found.'),
                APIResponseHelper::NOT_FOUND
            );
        }

        return response()->json(
            APIResponseHelper::success(
                APIResponseHelper::SUCCESS,
                'Module retrieved successfully.',
                $crudModule
            ),
            APIResponseHelper::SUCCESS
        );
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $crudModule = CrudModule::with([
            'fields',
            'relationships.relatedModule',
            'formLists.field',
            'permissions',
        ])->where('slug', $slug)->first();

        if (! $crudModule) {
            return response()->json(
                APIResponseHelper::error(APIResponseHelper::NOT_FOUND, 'Module not found.'),
                APIResponseHelper::NOT_FOUND
            );
        }

        return response()->json(
            APIResponseHelper::success(
                APIResponseHelper::SUCCESS,
                'Module retrieved successfully.',
                $crudModule
            ),
            APIResponseHelper::SUCCESS
        );
    }

    public function index(): JsonResponse
    {
        $modules = CrudModule::query()
            ->withCount(['fields', 'relationships', 'permissions'])
            ->latest()
            ->get();

        return response()->json(
            APIResponseHelper::success(
                APIResponseHelper::SUCCESS,
                'Modules retrieved successfully.',
                $modules
            ),
            APIResponseHelper::SUCCESS
        );
    }

    public function destroy(int $module, CrudModuleDeleterService $deleter): JsonResponse
    {
        try {
            $crudModule = CrudModule::find($module);
            
            if (! $crudModule) {
                return response()->json(
                    APIResponseHelper::error(APIResponseHelper::NOT_FOUND, 'Module not found.'),
                    APIResponseHelper::NOT_FOUND
                );
            }

            $deleter->delete($crudModule);

            return response()->json(
                APIResponseHelper::success(
                    APIResponseHelper::SUCCESS,
                    'Module and related files deleted successfully.'
                ),
                APIResponseHelper::SUCCESS
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(
                APIResponseHelper::error(APIResponseHelper::VALIDATION_ERROR, $e->getMessage()),
                APIResponseHelper::VALIDATION_ERROR
            );
        } catch (Throwable $e) {
            return response()->json(
                APIResponseHelper::error(
                    APIResponseHelper::SOMETHING_WENT_WRONG,
                    'Failed to delete module.',
                    ['exception' => $e->getMessage()]
                ),
                APIResponseHelper::SOMETHING_WENT_WRONG
            );
        }
    }
}
