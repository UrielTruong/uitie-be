<?php

use App\Http\Controllers\Api\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\StatisticController as AdminStatisticController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\AuthenticatedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

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
            // --- QUẢN LÝ BÀI ĐĂNG (Đã refactor theo flow của Manage Users) ---

            // Lấy list posts
            Route::get('post', [AdminPostController::class, 'getListPost']);

            // Duyệt bài
            Route::put('post/{id}/validate', [AdminPostController::class, 'validatePost']);

            //Quản lý báo cáo vi phạm
            Route::get('report', [AdminReportController::class, 'searchReport']);

            //Xem báo cáo statistic
            Route::get('/statistic', [AdminStatisticController::class, 'getStatistic']);

            //Validate report
            Route::put('report/{id}/validate', [AdminReportController::class, 'validateReport']);

            //--- EXPORT REPORT ---

            //export user pdf
            Route::get('/user/export-pdf', [AdminUserController::class, 'exportPdf']);

            //export post pdf
            Route::get('/post/export-pdf', [AdminPostController::class, 'exportPdf']);

            //export report pdf
            Route::get('/report/export-pdf', [AdminReportController::class, 'exportPdf']);

            //export statistic pdf
            Route::get('/statistic/export-pdf', [AdminStatisticController::class, 'exportPdf']);
        });
    });

    //route for USER
    Route::prefix('user')->group(function () {
        //change password
        Route::post('change-password', [UserController::class, 'changePassword']);
        //search user
        Route::get('/search', [UserController::class, 'search']);
    });


    //route for POST - FEED
    Route::prefix('post')->group(function () {
        Route::get('/', [PostController::class, 'getList']);
        Route::get('/search', [PostController::class, 'search']);
        Route::post('/', [PostController::class, 'create']);
        Route::put('/{id}', [PostController::class, 'update']);
        Route::delete('/{id}', [PostController::class, 'destroy']);
    });
});
