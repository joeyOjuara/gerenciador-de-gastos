<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CreditCardController;
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

Route::middleware(['auth', 'verified'])->prefix('accounts')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/store', [AccountController::class, 'store'])->name('accounts.store');
    Route::post('/transfer', [AccountController::class, 'transfer'])->name('accounts.transfer');
    Route::delete('/delete/{id}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    Route::put('/update/{id}', [AccountController::class, 'update'])->name('accounts.update');
});

Route::middleware(['auth', 'verified'])->prefix('credit-cards')->group(function () {
    Route::get('/', [CreditCardController::class, 'index'])->name('credit-cards.index');
    Route::post('/store', [CreditCardController::class, 'store'])->name('credit-cards.store');
    Route::delete('/delete/{id}', [CreditCardController::class, 'destroy'])->name('credit-cards.destroy');
    Route::put('/update/{id}', [CreditCardController::class, 'update'])->name('credit-cards.update');
    Route::post('/invoices/{invoiceId}/pay', [CreditCardController::class, 'payInvoice'])->name('credit-card-invoices.pay');
});

Route::middleware(['auth', 'verified'])->prefix('transactions')->group(function () {
    Route::get('/', [TransactionController::class, 'expenseIndex'])->name('transactions.index');
    Route::post('/store', [TransactionController::class, 'store'])->name('transactions.store');
    Route::delete('/delete/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::delete('/delete-bulk', [TransactionController::class, 'destroyBulk'])->name('transactions.destroyBulk');
    Route::put('/update/{id}', [TransactionController::class, 'update'])->name('transactions.update');
});

Route::middleware(['auth', 'verified'])->prefix('incomes')->group(function () {
    Route::get('/', [TransactionController::class, 'incomeIndex'])->name('incomes.index');
    Route::post('/store', [TransactionController::class, 'store'])->name('incomes.store');
    Route::delete('/delete/{id}', [TransactionController::class, 'destroy'])->name('incomes.destroy');
    Route::put('/update/{id}', [TransactionController::class, 'update'])->name('incomes.update');
});

require __DIR__ . '/auth.php';
