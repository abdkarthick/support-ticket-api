<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard & Notifications
    Route::get('/dashboard/stats', [DashboardController::class, 'index']);
    Route::get('/notifications', [DashboardController::class, 'notifications']);
    Route::post('/notifications/{id}/read', [DashboardController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [DashboardController::class, 'markAllAsRead']);

    Route::apiResource('tickets', TicketController::class);
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign']);
    Route::get('/tickets/{ticket}/replies', [TicketReplyController::class, 'index']);
    Route::post('/tickets/{ticket}/replies', [TicketReplyController::class, 'store']);
});
