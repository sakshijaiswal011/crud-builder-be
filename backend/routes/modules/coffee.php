<?php

use App\Http\Controllers\Api\CoffeeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('coffee', CoffeeController::class);
});
