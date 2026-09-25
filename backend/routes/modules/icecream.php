<?php

use App\Http\Controllers\Api\IcecreamController;
use Illuminate\Support\Facades\Route;

Route::apiResource('icecream', IcecreamController::class);
