<?php

use Illuminate\Support\Facades\Route;

// Chỉ những ai đã đăng nhập (auth:sanctum) và có quyền Admin (admin) mới vào được đây
Route::middleware(['auth:sanctum', 'admin'])->prefix('v1/admin')->group(function () {
    
    // Xem danh sách người dùng để quản lý và Khóa hoặc mở khóa tài khoản kèm lý do 
    Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
    Route::post('users/{id}/status', [\App\Http\Controllers\Admin\UserController::class, 'updateStatus']);

    // Kiểm duyệt bài viết của người dùng
    Route::get('posts/pending', [\App\Http\Controllers\Admin\PostController::class, 'getPendingPosts']);
    Route::post('posts/{id}/approve', [\App\Http\Controllers\Admin\PostController::class, 'approvePost']);
});