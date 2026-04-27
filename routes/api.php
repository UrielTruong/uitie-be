<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AuthenticatedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('login', [AuthenticatedController::class, 'login'])
        ->name('login');
});


Route::middleware('auth.jwt')->group(function () {
    Route::get('test', function (Request $request) {
        return response()->json([
            'message' => 'Hello World',
            'userId' => $request->user_id,
            'userRole' => $request->user_role,
        ]);
    });
});
