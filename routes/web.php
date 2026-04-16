<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;

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

Route::middleware(['auth', 'verified'])->prefix('payments')->group(function () {
    Route::get('/', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/store', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/delete/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::put('/update/{id}', [PaymentController::class, 'update'])->name('payments.update');
});

Route::middleware(['auth', 'verified'])->prefix('transactions')->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/store', [TransactionController::class, 'store'])->name('transactions.store');
    Route::delete('/delete/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::put('/update/{id}', [TransactionController::class, 'update'])->name('transactions.update');
});

Route::middleware(['auth', 'verified'])->prefix('incomes')->group(function () {
    Route::get('/', [TransactionController::class, 'incomeIndex'])->name('incomes.index');
    Route::post('/store', [TransactionController::class, 'store'])->name('incomes.store');
    Route::delete('/delete/{id}', [TransactionController::class, 'destroy'])->name('incomes.destroy');
    Route::put('/update/{id}', [TransactionController::class, 'update'])->name('incomes.update');
});

require __DIR__ . '/auth.php';
