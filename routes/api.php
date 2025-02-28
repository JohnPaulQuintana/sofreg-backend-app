<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\EmployeeAttendance;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Employee\EmployeeController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Admin Routes
    Route::prefix('admin')->group(function () {
        Route::post('/employee-account', [AccountController::class, 'addEmployeeAccount']);
        Route::get('/employee-account', [AccountController::class, 'getAllEmployeeAccount']);
        Route::get('/employee-attendance', [EmployeeAttendance::class, 'employeeAttendancePerMonth']);
        Route::get('/export-attendance', [EmployeeAttendance::class, 'exportAttendance']);
    });

    // Employee Routes
    Route::prefix('employee')->group(function () {
        Route::post('/clockin', [EmployeeController::class, 'clockInAttendance']);
        Route::post('/clockout', [EmployeeController::class, 'clockOutAttendance']);
        Route::get('/attendance-by-week', [EmployeeController::class, 'getAttendanceByWeek']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;



Route::get('/storage/{path}', function ($path) {
    $file = storage_path('app/public/' . $path);

    if (!file_exists($file)) {
        return response()->json([
            'error' => 'File not found',
            'path' => $file,
            'exists' => file_exists($file) ? 'Yes' : 'No'
        ], 404);
    }

    return response()->file($file, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
    ]);
})->where('path', '.*');

