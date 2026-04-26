<?php

use Illuminate\Support\Facades\Route;

// Chỉ những ai đã đăng nhập (auth:sanctum) và có quyền Admin (admin) mới vào được đây
Route::middleware(['auth:sanctum', 'admin'])->prefix('v1/admin')->group(function () {
    
    // Xem danh sách người dùng để quản lý 
    Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
    
    // Khóa hoặc mở khóa tài khoản kèm lý do [cite: 156, 174, 666]
    Route::post('users/{id}/status', [\App\Http\Controllers\Admin\UserController::class, 'updateStatus']);
});