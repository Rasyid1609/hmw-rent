<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FineFrontController;
use App\Http\Controllers\LoanFrontController;
use App\Http\Controllers\ProductFrontController;
use App\Http\Controllers\CategoryFrontController;
use App\Http\Controllers\ReturnProductFrontController;

Route::redirect('/', 'login');

Route::controller(DashboardController::class)->middleware(['auth', 'verified', 'dynamic.role_permission'])->group(function(){
    Route::get('dashboard', 'index')->name('dashboard');
});

Route::controller(ProductFrontController::class)->middleware(['auth', 'verified',  'dynamic.role_permission'])->group(function(){
    Route::get('products', 'index')->name('front.products.index');
    Route::get('products/{product:slug}', 'show')->name('front.products.show');
});

Route::controller(CategoryFrontController::class)->middleware(['auth', 'verified', 'dynamic.role_permission'])->group(function(){
    Route::get('categories', 'index')->name('front.categories.index');
    Route::get('categories/{category:slug}', 'show')->name('front.categories.show');
});

Route::controller(PaymentController::class)->group(function(){
    Route::post('payments', 'create')->name('payments.create');
    Route::post('payments/callback', 'callback')->name('payments.callback');
    Route::get('payments/success', 'success')->name('payments.success');
});

Route::controller(LoanFrontController::class)->middleware(['auth', 'verified', 'dynamic.role_permission'])->group(function(){
    Route::get('loans', 'index')->name('front.loans.index');
    Route::get('loans/{loan:loan_code}/detail', 'show')->name('front.loans.show');
    Route::post('loans/{product:slug}/create', 'store')->name('front.loans.store');
});

Route::controller(ReturnProductFrontController::class)->middleware(['auth', 'verified', 'dynamic.role_permission'])->group(function(){
    Route::get('return-products', 'index')->name('front.return-products.index');
    Route::get('return-products/{returnProduct:return_product_code}/detail', 'show')->name('front.return-products.show');
    Route::post('return-products/{product:slug}/create/{loan:loan_code}', 'store')->name('front.return-products.store');
});


Route::middleware(['auth', 'dynamic.role_permission'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('fines', FineFrontController::class)
    ->middleware(['auth', 'verified', 'role:member'])
    ->name('front.fines.index');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
