<?php

// routes/agency.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Agency\Auth\LoginController;
use App\Http\Controllers\Agency\DashboardController;
use App\Http\Controllers\Agency\StreamerController;
use App\Http\Controllers\Agency\ProfileController;


// routes/agency.php
Route::middleware('auth:agency')->group(function () {
     Route::delete('/streamers/{user}', [StreamerController::class, 'destroy'])->name('streamers.destroy');
});
Route::middleware('auth:agency')->prefix('streamers')->group(function () {
    Route::post('/', [StreamerController::class, 'store'])->name('streamers.store');
});

Route::middleware('auth:agency')->prefix('streamers')->name('streamers.')->group(function () {
    Route::get('{user}/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('{user}/profile', [ProfileController::class, 'update'])->name('profile.update');
});


Route::prefix('agency')->name('agency.')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::middleware('auth:agency')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
