<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return random json
    return response()->json([
        'status' => true
    ]);
});
