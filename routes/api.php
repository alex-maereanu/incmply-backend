<?php

use App\Http\Middleware\ForceJsonResponseMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([ForceJsonResponseMiddleware::class])->group(function () {
    Route::name('portal')->group(base_path('routes/api/portal/portal.php'));
});
