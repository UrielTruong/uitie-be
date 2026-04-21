<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('users', [UserController::class, 'getList']);
    Route::post('users', [UserController::class, 'createNew']);
});


