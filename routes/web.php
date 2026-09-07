<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});


// Breeze ke auth routes ke baad ya sath:
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

    Route::resource('accounts', AccountController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('transactions', TransactionController::class)->except(['show', 'edit', 'update']);
    Route::resource('budgets', BudgetController::class)->only(['index', 'store', 'destroy']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';
