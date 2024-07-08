<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;

Route::match(['get', 'post'], '/sign-in', [AuthController::class, 'signIn'])->name('signIn');

Route::middleware(['auth'])->group(function () {
    Route::view('/', 'docs');
    Route::view('/docs', 'docs')->name('docs');
    Route::get('/docs/insomnia.json', function () {
        return response()->file(storage_path('insomania/insomnia.json'));
    });
});
