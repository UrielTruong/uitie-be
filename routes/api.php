<?php

use App\Http\Controllers\Api\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\StatisticController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\AuthenticatedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::post('login', [AuthenticatedController::class, 'login'])
    ->name('login');

Route::post('reset-password', [UserController::class, 'resetPassword']);

Route::middleware('auth.jwt')->group(function () {

    Route::middleware('auth.role:ADMIN')->group(function () {
        Route::get('/admin', function () {
            return response()->json([
                'message' => 'Hello World',
            ]);
        });
    });
    Route::middleware('auth.role:SUPER_ADMIN')->group(function () {
        Route::get('/super-admin', function () {
            return response()->json([
                'message' => 'Hello World',
            ]);
        });
    });

    //route for admin
    Route::middleware('auth.role:ADMIN,SUPER_ADMIN')->group(function () {

        Route::prefix('admin')->group(function () {
            // Quản lý người dùng
            Route::get('user/search', [AdminUserController::class, 'searchUser']);

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
        //change password
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
