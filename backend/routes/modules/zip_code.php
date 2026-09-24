<?php

use App\Http\Controllers\Api\ZipCodeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('zip-code', ZipCodeController::class);
