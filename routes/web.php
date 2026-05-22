<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // License Management
    Route::get('/licenses', [DashboardController::class, 'list'])->name('licenses.index');
    Route::post('/licenses', [DashboardController::class, 'store'])->name('licenses.store');
    Route::get('/licenses/{license}', [DashboardController::class, 'show'])->name('licenses.show');
    Route::post('/licenses/{license}/generate-key', [DashboardController::class, 'generateKey'])->name('licenses.generate-key');
    Route::patch('/licenses/{license}/status', [DashboardController::class, 'updateStatus'])->name('licenses.status');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
