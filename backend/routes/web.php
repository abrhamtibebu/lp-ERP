<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Leather ERP API', 'version' => '1.0']);
});
