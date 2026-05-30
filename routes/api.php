<?php

use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StatisticController as AdminStatisticController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\FollowSuggestionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthenticatedController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthenticatedController::class, 'login'])
    ->name('login');

Route::post('reset-password', [UserController::class, 'resetPassword']);

// 2. PROTECTED ROUTES (Yêu cầu đăng nhập JWT)
Route::middleware('auth.jwt')->group(function () {

    //route for SUPER ADMIN
    Route::middleware('auth.role:Super Admin')->group(function () {
        Route::prefix('super-admin')->group(function () {
            // Manage users
            Route::get('user', [AdminUserController::class, 'searchUser']);
            Route::post('user', [AdminUserController::class, 'createNewUser']);
            Route::put('user/{id}', [AdminUserController::class, 'updateUser']);
            Route::delete('user/{id}', [AdminUserController::class, 'deleteUser']);
        });
    });

    //route for ADMIN
    Route::middleware('auth.role:Admin,Super Admin')->group(function () {
        Route::prefix('admin')->group(function () {
            // --- QUẢN LÝ TÀI KHOẢN (Lock/Unlock) ---
            Route::put('user/{id}/lock', [AdminUserController::class, 'lockUser']);
            Route::put('user/{id}/unlock', [AdminUserController::class, 'unlockUser']);

            // --- QUẢN LÝ BÀI ĐĂNG ---
            Route::get('post', [AdminPostController::class, 'getListPost']);
            Route::put('post/{id}/validate', [AdminPostController::class, 'validatePost']);

            //Quản lý báo cáo vi phạm
            Route::get('report', [AdminReportController::class, 'searchReport']);
            Route::put('report/{id}/validate', [AdminReportController::class, 'validateReport']);

            //Xem báo cáo statistic
            Route::get('/statistic', [AdminStatisticController::class, 'getStatistic']);

            //--- EXPORT REPORT ---
            Route::get('/user/export-pdf', [AdminUserController::class, 'exportPdf']);
            Route::get('/post/export-pdf', [AdminPostController::class, 'exportPdf']);
            Route::get('/report/export-pdf', [AdminReportController::class, 'exportPdf']);
            Route::get('/statistic/export-pdf', [AdminStatisticController::class, 'exportPdf']);
        });
    });

    //route for USER
    Route::prefix('user')->group(function () {
        //change password
        Route::post('change-password', [UserController::class, 'changePassword']);
        //search user
        Route::get('/search', [UserController::class, 'search']);

        // Route báo cáo người dùng 
        Route::post('/{id}/report', [ReportController::class, 'reportUser']);

        // Lấy thông tin profile của chính mình và người khác trong UserController
        Route::get('profile', [UserController::class, 'profile']);
        Route::get('/{id}', [UserController::class, 'show']);
    });

    // 
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);

    // Lấy danh sách bài viết của một user cụ thể
    Route::get('/users/{id}/posts', [PostController::class, 'getUserPosts']);

    // Gợi ý follow
    Route::get('/users/suggested-follows', [FollowSuggestionController::class, 'getSuggestedFollows']);


    //route for POST - FEED
    Route::prefix('post')->group(function () {
        Route::get('/', [PostController::class, 'getList']);
        Route::get('/search', [PostController::class, 'search']);
        Route::post('/', [PostController::class, 'create']);
        Route::put('/{id}', [PostController::class, 'update']);
        Route::delete('/{id}', [PostController::class, 'destroy']);

        // Báo cáo bài viết (Đã cập nhật theo Controller mới)
        Route::post('/{id}/report', [ReportController::class, 'reportPost']);
    });

    //route for COMMENT
    Route::get('/posts/{postId}/comments', [CommentController::class, 'getByPost']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    //route for CATEGORY
    Route::prefix('categories')->group(function () {
        Route::get('/trending', [CategoryController::class, 'getTrending']);
    });

    //route for ATTACHMENT
    Route::prefix('attachment')->group(function () {
        Route::post('/presign', [AttachmentController::class, 'presign']);
    });
});
