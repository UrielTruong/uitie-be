<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return random json
    return response()->json([
        'message' => "Add header Accept to api route"
    ]);
});
