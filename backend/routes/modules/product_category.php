<?php

use App\Http\Controllers\Api\ProductCategoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource('product-categories', ProductCategoryController::class);
