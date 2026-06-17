<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ReviewController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/organization', [OrganizationController::class, 'store']);
    Route::get('/organization', [OrganizationController::class, 'show']);
    Route::post('/organization/{id}/delete', [OrganizationController::class, 'delete']);

    Route::get('/organizations/{organization}/reviews',[ReviewController::class, 'index']);
    Route::post('/reviews/parse',[ReviewController::class, 'parse']);

});