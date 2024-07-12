<?php

use App\Http\Middleware\ForceJsonResponseMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([ForceJsonResponseMiddleware::class])->group(function () {
    require base_path('routes/api/center/base.php');
});
