<?php

use App\Http\Controllers\AuthenticatedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PostController as AdminPostController;

Route::post('login', [AuthenticatedController::class, 'login'])
    ->name('login');

//reset password
Route::post('reset-password', [UserController::class, 'resetPassword']);

Route::middleware('auth.jwt')->group(function () {
    Route::get('test', function (Request $request) {
        return response()->json([
            'message' => 'Hello World',
            'userId' => $request->user_id,
            'userRole' => $request->user_role,
        ]);
    });

    Route::middleware('auth.role:Admin')->group(function () {
        Route::get('/admin', function () {
            return response()->json([
                'message' => 'Hello World',
            ]);
        });

        // --- CODE CỦA THƯ ---
        // Quản lý User
        Route::get('admin/users', [AdminUserController::class, 'index']);
        Route::post('admin/users/{id}/status', [AdminUserController::class, 'updateStatus']);

        // Kiểm duyệt bài đăng
        Route::get('admin/posts/pending', [AdminPostController::class, 'getPendingPosts']);
        Route::post('admin/posts/{id}/approve', [AdminPostController::class, 'approvePost']);
        // --------------------------------
    });

    });
    Route::middleware('auth.role:Super Admin')->group(function () {
        Route::get('/super-admin', function () {
            return response()->json([
                'message' => 'Hello World',
            ]);
        });

        // --- CODE CỦA THư ---
        Route::get('super-admin/manage-admins', [AdminUserController::class, 'getAdminList']);
        Route::post('super-admin/manage-admins', [AdminUserController::class, 'createAdmin']);
        // --------------------------------
    });

    //route for user
    Route::prefix('user')->group(function () {
        //change password
        Route::post('change-password', [UserController::class, 'changePassword']);
    });
