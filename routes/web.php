<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/api/documentation');
});

Route::fallback(function () {
    return redirect('/api/documentation');
});
