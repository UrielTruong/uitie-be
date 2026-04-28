<?php

use App\Http\Controllers\AuthenticatedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

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
    });
    Route::middleware('auth.role:Super Admin')->group(function () {
        Route::get('/super-admin', function () {
            return response()->json([
                'message' => 'Hello World',
            ]);
        });
    });

    //route for user
    Route::prefix('user')->group(function () {
        //change password
        Route::post('change-password', [UserController::class, 'changePassword']);
    });
});
