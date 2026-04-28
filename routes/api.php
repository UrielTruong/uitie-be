<?php

use App\Http\Controllers\AuthenticatedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthenticatedController::class, 'login'])
    ->name('login');

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
});
