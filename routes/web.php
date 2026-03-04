<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;

Route::redirect('/', '/login');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/store', [CategoryController::class, 'store'])->name('categories.store');
    Route::delete('/delete/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::put('/update/{id}', [CategoryController::class, 'update'])->name('categories.update');
});

route::middleware(['auth', 'verified'])->prefix('transactions')->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/store', [TransactionController::class, 'store'])->name('transactions.store');
    Route::delete('/delete/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::put('/update/{id}', [TransactionController::class, 'update'])->name('transactions.update');
});

require __DIR__ . '/auth.php';
