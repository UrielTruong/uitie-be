<?php

use App\Http\Controllers\Api\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\StatisticController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\AuthenticatedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

Route::post('login', [AuthenticatedController::class, 'login'])
    ->name('login');

Route::post('reset-password', [UserController::class, 'resetPassword']);

// 2. PROTECTED ROUTES (Yêu cầu đăng nhập JWT)
Route::middleware('auth.jwt')->group(function () {

    //route for SUPER ADMIN
    Route::middleware('auth.role:SUPER_ADMIN')->group(function () {
        Route::prefix('super-admin')->group(function () {
            Route::get('manage-admins', [AdminUserController::class, 'getAdminList']);
            Route::post('manage-admins', [AdminUserController::class, 'createAdmin']);
            Route::put('manage-admins/{id}', [AdminUserController::class, 'updateAdmin']);
            Route::delete('manage-admins/{id}', [AdminUserController::class, 'deleteAdmin']);
        });
    });

    //route for admin
    Route::middleware('auth.role:ADMIN,SUPER_ADMIN')->group(function () {

        Route::prefix('admin')->group(function () {
            // Quản lý người dùng
            Route::get('user/search', [AdminUserController::class, 'searchUser']);

            // --- QUẢN LÝ USER (SINH VIÊN) ---
            Route::get('admin/users', [AdminUserController::class, 'index']);
            Route::post('admin/users/{id}/status', [AdminUserController::class, 'updateStatus']);

            // Mới thêm: Cập nhật thông tin và Xóa sinh viên
            Route::put('admin/users/{id}', [AdminUserController::class, 'updateStudent']);
            Route::delete('admin/users/{id}', [AdminUserController::class, 'deleteStudent']);

            // --- KIỂM DUYỆT BÀI ĐĂNG ---
            Route::get('admin/posts/pending', [AdminPostController::class, 'getPendingPosts']);
            Route::post('admin/posts/{id}/approve', [AdminPostController::class, 'approvePost']);

            // Mới thêm: Xóa bài viết vi phạm
            Route::delete('admin/posts/{id}', [AdminPostController::class, 'deletePost']);

            // Quản lý bài viết
            Route::get('post/search', [AdminPostController::class, 'searchPost']);

            //Quản lý báo cáo vi phạm
            Route::get('report', [AdminReportController::class, 'searchReport']);

            //Xem báo cáo statistic
            Route::get('/statistic', [StatisticController::class, 'getStatistic']);

            //Validate report
            Route::put('report/{id}/validate', [AdminReportController::class, 'validateReport']);

            //export user pdf
            Route::get('/user/export-pdf', [AdminUserController::class, 'exportPdf']);

            //export post pdf
            Route::get('/post/export-pdf', [AdminPostController::class, 'exportPdf']);

            //export report pdf
            Route::get('/report/export-pdf', [AdminReportController::class, 'exportPdf']);

            //export statistic pdf
            Route::get('/statistic/export-pdf', [StatisticController::class, 'exportPdf']);
        });
    });

    //route for user
    Route::prefix('user')->group(function () {
        Route::post('change-password', [UserController::class, 'changePassword']);
    });

    //route for user search
    Route::prefix('user')->group(function () {
        Route::get('/search', [UserController::class, 'search']);
    });

    //route for post
    Route::prefix('post')->group(function () {
        Route::get('/', [PostController::class, 'getList']);
        Route::get('/search', [PostController::class, 'search']);
        Route::post('/', [PostController::class, 'create']);
        Route::put('/{id}', [PostController::class, 'update']);
        Route::delete('/{id}', [PostController::class, 'destroy']);
    });
});
