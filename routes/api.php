<?php

use App\Http\Controllers\AuthenticatedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PostController as AdminPostController;

// 1. PUBLIC ROUTES
Route::post('login', [AuthenticatedController::class, 'login'])->name('login');
Route::post('reset-password', [UserController::class, 'resetPassword']);

// 2. PROTECTED ROUTES (Yêu cầu đăng nhập JWT)
Route::middleware('auth.jwt')->group(function () {
    
    Route::get('test', function (Request $request) {
        return response()->json([
            'message' => 'Hello World',
            'userId' => $request->user_id,
            'userRole' => $request->user_role,
        ]);
    });

    // NHÓM DÀNH CHO ADMIN
    Route::middleware('auth.role:Admin')->group(function () {
        Route::get('/admin', function () {
            return response()->json(['message' => 'Hello World']);
        });

        // Quản lý User (Sinh viên)
        Route::get('admin/users', [AdminUserController::class, 'index']);
        Route::post('admin/users/{id}/status', [AdminUserController::class, 'updateStatus']);

        // Kiểm duyệt bài đăng
        Route::get('admin/posts/pending', [AdminPostController::class, 'getPendingPosts']);
        Route::post('admin/posts/{id}/approve', [AdminPostController::class, 'approvePost']);
    });

    // NHÓM DÀNH CHO SUPER ADMIN (Quản lý các Admin khác)
    Route::middleware('auth.role:Super Admin')->group(function () {
        Route::get('/super-admin', function () {
            return response()->json(['message' => 'Hello World']);
        });

        // Task: Tạo và quản lý tài khoản Admin
        Route::get('super-admin/manage-admins', [AdminUserController::class, 'getAdminList']);
        Route::post('super-admin/manage-admins', [AdminUserController::class, 'createAdmin']);
        
        // --- SUPER ADMIN CẬP NHẬT VÀ XÓA ADMIN---
        Route::put('super-admin/manage-admins/{id}', [AdminUserController::class, 'updateAdmin']);
        Route::delete('super-admin/manage-admins/{id}', [AdminUserController::class, 'deleteAdmin']);
    });

    // NHÓM DÀNH CHO USER THƯỜNG
    Route::prefix('user')->group(function () {
        Route::post('change-password', [UserController::class, 'changePassword']);
    });

});