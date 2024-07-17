<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tenant\CasesController;

Route::name('.cases')->prefix('/cases')->group(function () {
    Route::middleware(['auth.verified'])->group(function () {
        Route::get('/all', [CasesController::class, 'index'])->name('.all');
    });
});
