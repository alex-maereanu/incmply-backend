<?php

use App\Http\Controllers\Api\Center\AuthController;
use Illuminate\Support\Facades\Route;

Route::name('.auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('.register');
    Route::post('/sign-in', [AuthController::class, 'signIn'])->name('.signIn');
    Route::post('/forgot-password', [AuthController ::class, 'forgotPassword'])->name('.forgotPassword');
    Route::post('/verify-token', [AuthController ::class, 'verifyChangePasswordToken'])->name('.verifyChangePasswordToken');
    Route::post('/change-password', [AuthController ::class, 'changePassword'])->name('.changePassword');
    Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);

    Route::post('email/verify/{id}/{hash}', [AuthController::class, 'verifyMail'])
         ->name('.verification.verify')
         ->middleware(['signed']);
});