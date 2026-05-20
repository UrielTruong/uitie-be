<?php

use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StatisticController as AdminStatisticController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthenticatedController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;

// 1. PUBLIC ROUTES (Không yêu cầu đăng nhập)
Route::post('login', [AuthenticatedController::class, 'login'])
    ->name('login');

Route::post('reset-password', [UserController::class, 'resetPassword']);

// 2. PROTECTED ROUTES (Yêu cầu đăng nhập thông qua JWT bảo mật của dự án)
Route::middleware('auth.jwt')->group(function () {

    // --- LUỒNG DÀNH CHO SUPER ADMIN ---
    Route::middleware('auth.role:Super Admin')->group(function () {
        Route::prefix('super-admin')->group(function () {
            // Manage users
            Route::get('user', [AdminUserController::class, 'searchUser']);
            Route::post('user', [AdminUserController::class, 'createNewUser']);
            Route::put('user/{id}', [AdminUserController::class, 'updateUser']);
            Route::delete('user/{id}', [AdminUserController::class, 'deleteUser']);
        });
    });

    // --- LUỒNG DÀNH CHO ADMIN & SUPER ADMIN ---
    Route::middleware('auth.role:Admin,Super Admin')->group(function () {
        Route::prefix('admin')->group(function () {
            // Quản lý bài đăng
            Route::get('post', [AdminPostController::class, 'getListPost']);
            Route::put('post/{id}/validate', [AdminPostController::class, 'validatePost']);

            // Quản lý báo cáo vi phạm
            Route::get('report', [AdminReportController::class, 'searchReport']);
            Route::put('report/{id}/validate', [AdminReportController::class, 'validateReport']);

            // Xem báo cáo statistic
            Route::get('/statistic', [AdminStatisticController::class, 'getStatistic']);

            // Xuất báo cáo (Export PDF)
            Route::get('/user/export-pdf', [AdminUserController::class, 'exportPdf']);
            Route::get('/post/export-pdf', [AdminPostController::class, 'exportPdf']);
            Route::get('/report/export-pdf', [AdminReportController::class, 'exportPdf']);
            Route::get('/statistic/export-pdf', [AdminStatisticController::class, 'exportPdf']);
        });
    });

    // --- LUỒNG DÀNH CHO USER CƠ BẢN (STUDENT) ---
    Route::prefix('user')->group(function () {
        Route::post('change-password', [UserController::class, 'changePassword']);
        Route::get('/search', [UserController::class, 'search']);
        Route::get('/profile', [ProfileController::class, 'getProfile']);
        Route::put('/profile', [ProfileController::class, 'updateProfile']);
    });

    // Lấy danh sách bài viết của một user cụ thể
    Route::get('/users/{id}/posts', [PostController::class, 'getUserPosts']);

    // --- LUỒNG QUẢN LÝ BÀI VIẾT VÀ BẢNG TIN (POST - FEED) ---
    Route::prefix('post')->group(function () {
        Route::get('/', [PostController::class, 'getList']);
        Route::get('/search', [PostController::class, 'search']);
        Route::post('/', [PostController::class, 'create']);
        Route::put('/{id}', [PostController::class, 'update']);
        Route::delete('/{id}', [PostController::class, 'destroy']);
    });

    // --- LUỒNG ĐÍNH KÈM TÀI LIỆU (ATTACHMENT) ---
    Route::prefix('attachment')->group(function () {
        Route::post('/presign', [AttachmentController::class, 'presign']);
    });

    // =========================================================================
    // PHÂN HỆ MỚI: KẾT NỐI CỘNG ĐỒNG (ĐƯỢC BẢO VỆ BỞI JWT MIDDLEWARE)
    // =========================================================================
    Route::post('/user/{id}/follow', [FollowController::class, 'toggle']);
    Route::get('/posts/feed/followers', [FollowController::class, 'followersFeed']);
});