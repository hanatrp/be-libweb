<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ChatController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/settings', [SettingsController::class, 'index']);
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/profile/update', [AuthController::class, 'updateProfile']);
    
    Route::post('/settings', [SettingsController::class, 'update']);
    
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/{id}/restore', [UserController::class, 'restore']);
    
    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);
    Route::post('/books/{id}/restore', [BookController::class, 'restore']);
    
    Route::get('/loans', [LoanController::class, 'index']);
    Route::post('/loans/borrow', [LoanController::class, 'store']);
    Route::post('/loans/{id}/return', [LoanController::class, 'returnBook']);
    Route::post('/loans/{id}/approve', [LoanController::class, 'approve']);
    
    Route::get('/reports/analytics', [AnalyticsController::class, 'index']);
    Route::get('/chat', [ChatController::class, 'index']);
    Route::delete('/chat', [ChatController::class, 'destroy']);
    Route::post('/chat', [ChatController::class, 'store']);
});
