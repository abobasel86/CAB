<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\FieldSettingController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\ExportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'user'])->middleware('auth:sanctum');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Transaction routes
    Route::apiResource('transactions', TransactionController::class);
    
    // Field settings routes
    Route::apiResource('field-settings', FieldSettingController::class);
    Route::get('/field-config', [FieldSettingController::class, 'getFieldConfig']);
    
    // Import routes
    Route::post('/import/transactions', [ImportController::class, 'importTransactions']);
    Route::get('/import/template', [ImportController::class, 'downloadTemplate']);
    
    // Export routes
    Route::get('/export/excel', [ExportController::class, 'exportToExcel']);
    Route::get('/export/pdf', [ExportController::class, 'exportToPdf']);
});
