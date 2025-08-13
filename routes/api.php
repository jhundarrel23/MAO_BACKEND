<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\AdminSanctumController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\API\Coordinator\CoordinatorController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public admin routes (no auth required)
Route::post('/admin/login', [AdminSanctumController::class, 'login']);
Route::post('/admin/register', [AdminSanctumController::class, 'register']);
Route::post('/coordinator/login', [CoordinatorController::class, 'login']);
// Protected routes (require login)
Route::middleware('auth:sanctum')->group(function () {
    // Admin logout
    Route::post('/admin/logout', [AdminSanctumController::class, 'logout']);

    // Sector CRUD + custom routes
    Route::apiResource('sectors', SectorController::class);
    Route::get('/sectors/check-name', [SectorController::class, 'checkName']);
    Route::get('/sectors/with-trashed', [SectorController::class, 'allWithTrashed']);
    Route::post('/sectors/{id}/restore', [SectorController::class, 'restore']);

    // Coordinator routes

    Route::get('/coordinators', [CoordinatorController::class, 'index']);   // ✅ fetch list of coordinators
    Route::post('/coordinators', [CoordinatorController::class, 'store']);  // ✅ create a new coordinator
});
