<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tenant\SettingsController;

Route::name('.settings')->prefix('/settings')->group(function () {
    Route::middleware(['auth.verified'])->group(function () {
        Route::get('/generate-qr-code', [SettingsController::class, 'generateQrCode'])->name('.generateQrCode');
        Route::patch('/enable-2fa', [SettingsController::class, 'enableOtp']);
    });
});