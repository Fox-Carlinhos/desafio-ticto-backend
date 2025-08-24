<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\TimeRecordController;
use App\Http\Controllers\Web\ReportController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
Route::get('/employee/dashboard', [DashboardController::class, 'employee'])->name('employee.dashboard');

Route::prefix('admin')->group(function () {
    Route::resource('employees', EmployeeController::class);
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
});

Route::get('time-records', [TimeRecordController::class, 'index'])->name('time-records.index');
Route::post('time-records', [TimeRecordController::class, 'store'])->name('time-records.store');

Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
Route::get('/settings', [AuthController::class, 'settings'])->name('settings');
