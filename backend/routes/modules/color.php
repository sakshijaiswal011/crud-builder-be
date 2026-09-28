<?php

use App\Http\Controllers\Api\ColorController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('color', ColorController::class);
});
