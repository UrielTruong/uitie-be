<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AuthenticatedController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('login', [AuthenticatedController::class, 'login'])
        ->name('login');
});
