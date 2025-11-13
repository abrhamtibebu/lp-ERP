<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Parker Clay ERP API', 'version' => '1.0']);
});
