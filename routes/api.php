<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\DashboardController;

// User Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [UserController::class, 'me']);
    Route::put('/me', [UserController::class, 'update']);
});
Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'index']);

/*
|--------------------------------------------------------------------------
| DASHBOARD (ADMIN & REVIEWER & STAFF)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});


/*
|--------------------------------------------------------------------------
| INCIDENTS AUTHENTICATED ACCESS (ALL ROLES)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {
    // Everyone can view incidents (admin, reviewer, staff)
    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::get('/incidents/{id}', [IncidentController::class, 'show']);
    // Staff specific (assigned incidents)
    Route::get('/assigned-incidents', [IncidentController::class, 'assignedIncidents']);
});
// Admin have full access to create, update, delete
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/incidents', [IncidentController::class, 'store']);
    Route::put('/incidents/{id}', [IncidentController::class, 'update']);
    Route::delete('/incidents/{id}', [IncidentController::class, 'destroy']);
});
// Reviewer can update status, assign, add notes
Route::middleware(['auth:sanctum', 'role:reviewer'])->group(function () {
    Route::put('/incidents/{id}', [IncidentController::class, 'reviewerUpdate']);
});
// Staff can create incidents, and update status of their assigned incidents
Route::middleware(['auth:sanctum', 'role:staff'])->group(function () {
    Route::put('/incidents/{id}/status', [IncidentController::class, 'updateStatus']);
    Route::post('/incidents', [IncidentController::class, 'store']);
});
// Rpa can create incidents and view rpa logs
Route::post('/rpa/incidents', [IncidentController::class, 'storeRPA']);
Route::get('/incidents/{id}/rpa-logs', [IncidentController::class, 'viewRpaLogs']);


/*
|--------------------------------------------------------------------------
| USER SETTINGS (ALL ROLES)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // 👤 get profile
    Route::get('/me', [UserController::class, 'me']);

    // ✏️ update profile
    Route::put('/me', [UserController::class, 'update']);

    // 🚪 logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| OPEN AI CONTROLLER (NO USER ACCESS)
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/ai/process', [AIController::class, 'process']);
});
