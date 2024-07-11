<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Portal\AuthController as ClientAuthController;
use App\Models\Tenant\Role;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/'], function () {
    Route::post('/sign-in', [ClientAuthController::class, 'signIn']);
    Route::post('/forgot-password', [AuthController ::class, 'forgotPassword']);
    Route::post('/verify-token', [AuthController ::class, 'verifyChangePasswordToken']);
    Route::post('/change-password', [AuthController ::class, 'changePassword']);
    Route::post('register', [ClientAuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);

    Route::post('email/verify/{id}/{hash}', [AuthController::class, 'verifyMail'])
         ->name('verification.verify')
         ->middleware(['signed']);

    Route::middleware(['auth.verified', 'role:' . implode('|', Role::ROLES_PORTAL_ROLES)])
         ->group(function () {
             Route::get('/generate-qr-code', [AuthController::class, 'generateQrCode']);
             Route::get('/sign-out', [AuthController ::class, 'signOut']);
         });
});
