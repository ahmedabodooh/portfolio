<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['throttle:api'])->group(function () {
    require __DIR__ . '/api/public.php';
    require __DIR__ . '/api/admin.php';
});
