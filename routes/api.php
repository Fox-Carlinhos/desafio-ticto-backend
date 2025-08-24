<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TimeRecordController;
use App\Http\Controllers\ReportController;

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

        Route::prefix('admin/time-records')->group(function () {
            Route::get('/', [TimeRecordController::class, 'adminIndex']);
            Route::get('/employee/{employee}', [TimeRecordController::class, 'adminEmployeeRecords']);
        });

        Route::prefix('reports')->group(function () {
            Route::get('/time-records', [ReportController::class, 'timeRecordsReport']);
            Route::get('/summary', [ReportController::class, 'summary']);
            Route::get('/export', [ReportController::class, 'export']);
        });
    });

    Route::get('/profile', [AuthController::class, 'profile']);

    Route::middleware('employee')->group(function () {
        Route::prefix('time-records')->group(function () {
            Route::post('/', [TimeRecordController::class, 'store']);
            Route::get('/', [TimeRecordController::class, 'index']);
            Route::get('/summary', [TimeRecordController::class, 'summary']);
            Route::get('/today', [TimeRecordController::class, 'todayStatus']);
        });
    });
});
