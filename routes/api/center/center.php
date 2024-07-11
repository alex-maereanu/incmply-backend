<?php

use Illuminate\Support\Facades\Route;

Route::name('center')->group(function () {
    require base_path('routes/api/center/auth.php');

    // TODO: delete function and route
    if(config('app.env') === 'local'){
        Route::delete('/delete-all', [TenantController::class, 'deleteAll'])->name('.delete-all');
    }
});