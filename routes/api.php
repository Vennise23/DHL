<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IncidentController;

// User Authentication
Route::post('/login', [AuthController::class, 'login']);

// Incidents API
Route::get('/incidents', [IncidentController::class, 'index']);
Route::get('/incidents/{id}', [IncidentController::class, 'show']);
Route::post('/incidents', [IncidentController::class, 'store']);
Route::put('/incidents/{id}', [IncidentController::class, 'update']);
Route::delete('/incidents/{id}', [IncidentController::class, 'destroy']);