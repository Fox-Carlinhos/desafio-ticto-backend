<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TimeRecordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);


    Route::middleware('admin')->group(function () {
        Route::apiResource('employees', EmployeeController::class);
        Route::prefix('reports')->group(function () {
        });
    });

    Route::middleware('employee')->group(function () {
        Route::prefix('time-records')->group(function () {
            Route::post('/', [TimeRecordController::class, 'store']);
            Route::get('/', [TimeRecordController::class, 'index']);
            Route::get('/summary', [TimeRecordController::class, 'summary']);
            Route::get('/today', [TimeRecordController::class, 'todayStatus']);
        });
    });
});
