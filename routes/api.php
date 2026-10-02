<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\LoginSessionController;



// CSRF Cookie
Route::get('/sanctum/csrf-cookie', function (Request $request) {
    return response()->json(['message' => 'CSRF cookie set']);
});

// Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('auth:sanctum');
Route::get('/user-sessions/{user_id}', [AuthController::class, 'getUserSessions'])->middleware('auth:sanctum');
Route::get('/login-sessions', [LoginSessionController::class, 'index'])->middleware('auth:sanctum');

// Password Reset (public routes)
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/password/validate-token', [PasswordResetController::class, 'validateToken']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

// Protected routes with authentication
Route::middleware('auth:sanctum')->group(function () {

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::put('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::get('/users/roles', [UserController::class, 'getRoles']);
    Route::post('/users/reset-password', [UserController::class, 'resetPassword']);

    // Roles
    Route::get('/roles', [RoleController::class, 'index']);
        // login sessions
    Route::get('/login-sessions', [LoginSessionController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
    // Super admin specific routes
});


//Create Product
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);