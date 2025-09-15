<?php

use App\Helpers\Response\AppResponse;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Middlewares\Authenticate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/**
 * Default Route - /
 */
Route::get('/', function () {
    return response()->json([
        'code' => Response::HTTP_OK,
        'message' => 'Welcome to ' . config('app.name') . ' ' . env('APP_VERSION', '1.0'),
    ], Response::HTTP_OK);
});

/**
 * Route Fallback - for any endpoints not supported by the system
 */
Route::fallback(function () {
    return AppResponse::sendError(
        statusCode: Response::HTTP_NOT_FOUND,
        errorMessages: 'Endpoint not found. Please check the URL.'
    );
});

// Authentication Routes
Route::middleware(Authenticate::class)->group(function () {
    Route::apiResource('tags', TagController::class);
    Route::apiResource('tasks', TaskController::class);

    // For toggle status
    Route::patch('tasks/{taskId}/toggle-status', [TaskController::class, 'toggleStatus']);

    // For restore
    Route::patch('tasks/{taskId}/restore', [TaskController::class, 'restore']);
});
