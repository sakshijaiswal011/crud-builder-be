<?php

use App\Http\Controllers\Api\CrudModuleController;
use Illuminate\Support\Facades\Route;

Route::get('/test-api', function () {
    return response()->json([
        'message' => 'CRUD Builder API',
    ]);
});

Route::prefix('crud-modules')->group(function () {
    Route::get('/', [CrudModuleController::class, 'index']);
    Route::post('/create', [CrudModuleController::class, 'store']);
    Route::get('/slug/{slug}', [CrudModuleController::class, 'showBySlug']);
    Route::get('/{module}', [CrudModuleController::class, 'show']);
});

foreach (glob(__DIR__.'/modules/*.php') ?: [] as $moduleRouteFile) {
    require $moduleRouteFile;
}
