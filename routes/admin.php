<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\FineController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AssignUserController;
use App\Http\Controllers\Admin\FineReportController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\FineSettingController;
use App\Http\Controllers\Admin\RouteAccessController;
use App\Http\Controllers\Admin\ProductStockController;
use App\Http\Controllers\Admin\LoanStatisticController;
use App\Http\Controllers\Admin\ReturnProductController;
use App\Http\Controllers\Admin\AssignPermissionController;
use App\Http\Controllers\Admin\ProductStockReportController;


Route::middleware(['auth', 'role:admin|operator'])->prefix('admin')->name('admin.')->group(function () {

    Route::controller(LoanStatisticController::class)->group(function() {
        Route::get('loan-statistics', 'index')->name('loan-statistics.index');
    });

    Route::controller(FineReportController::class)->group(function() {
        Route::get('fine-reports', 'index')->name('fine-reports.index');
    });

    Route::controller(ProductStockReportController::class)->group(function(){
        Route::get('product-stock-reports', 'index')->name('product-stock-reports.index');
        Route::get('product-stock-reports/edit/{stock}', 'edit')->name('product-stock-reports.edit');
        Route::put('product-stock-reports/edit/{stock}', 'update')->name('product-stock-reports.update');
    });

    Route::controller(CategoryController::class)->group(function(){
        Route::get('categories', 'index')->name('categories.index');
        Route::get('categories/create', 'create')->name('categories.create');
        Route::post('categories/create', 'store')->name('categories.store');
        Route::get('categories/edit/{category}', 'edit')->name('categories.edit');
        Route::put('categories/edit/{category}', 'update')->name('categories.update');
        Route::delete('categories/destroy/{category}', 'destroy')->name('categories.destroy');
    });

    Route::controller(BrandController::class)->group(function(){
        Route::get('brands', 'index')->name('brands.index');
        Route::get('brands/create', 'create')->name('brands.create');
        Route::post('brands/create', 'store')->name('brands.store');
        Route::get('brands/edit/{brand}', 'edit')->name('brands.edit');
        Route::put('brands/edit/{brand}', 'update')->name('brands.update');
        Route::delete('brands/destroy/{brand}', 'destroy')->name('brands.destroy');
    });

    Route::controller(ProductController::class)->group(function(){
        Route::get('products', 'index')->name('products.index');
        Route::get('products/create', 'create')->name('products.create');
        Route::post('products/create', 'store')->name('products.store');
        Route::get('products/edit/{product}', 'edit')->name('products.edit');
        Route::put('products/edit/{product}', 'update')->name('products.update');
        Route::delete('products/destroy/{product}', 'destroy')->name('products.destroy');
    });

    Route::controller(UserController::class)->group(function(){
        Route::get('users', 'index')->name('users.index');
        Route::get('users/create', 'create')->name('users.create');
        Route::post('users/create', 'store')->name('users.store');
        Route::get('users/edit/{user}', 'edit')->name('users.edit');
        Route::put('users/edit/{user}', 'update')->name('users.update');
        Route::delete('users/destroy/{user}', 'destroy')->name('users.destroy');
    });

    Route::controller(FineSettingController::class)->group(function(){
        Route::get('fine-settings/create', 'create')->name('fine-settings.create');
        Route::put('fine-settings/create', 'store')->name('fine-settings.store');
    });

    Route::controller(LoanController::class)->group(function(){
        Route::get('loans', 'index')->name('loans.index');
        Route::get('loans/create', 'create')->name('loans.create');
        Route::post('loans/create', 'store')->name('loans.store');
        Route::get('loans/edit/{loan}', 'edit')->name('loans.edit');
        Route::put('loans/edit/{loan}', 'update')->name('loans.update');
        Route::delete('loans/destroy/{loan}', 'destroy')->name('loans.destroy');
    });

    Route::controller(ReturnProductController::class)->group(function(){
        Route::get('return-products', 'index')->name('return-products.index');
        Route::get('return-products/{loan:loan_code}/create', 'create')->name('return-products.create');
        Route::put('return-products/{loan:loan_code}/create', 'store')->name('return-products.store');
        Route::put('return-products/{returnproduct:return_product_code}/approve', 'approve')->name('return-products.approve');
    });

    Route::controller(FineController::class)->group(function(){
        Route::get('fines/{returnproduct:return_product_code}/create', 'create')->name('fines.create');
    });

    Route::controller(RoleController::class)->group(function(){
        Route::get('roles', 'index')->name('roles.index');
        Route::get('roles/create', 'create')->name('roles.create');
        Route::post('roles/create', 'store')->name('roles.store');
        Route::get('roles/edit/{role}', 'edit')->name('roles.edit');
        Route::put('roles/edit/{role}', 'update')->name('roles.update');
        Route::delete('roles/destroy/{role}', 'destroy')->name('roles.destroy');
    });

    Route::controller(PermissionController::class)->group(function(){
        Route::get('permissions', 'index')->name('permissions.index');
        Route::get('permissions/create', 'create')->name('permissions.create');
        Route::post('permissions/create', 'store')->name('permissions.store');
        Route::get('permissions/edit/{permission}', 'edit')->name('permissions.edit');
        Route::put('permissions/edit/{permission}', 'update')->name('permissions.update');
        Route::delete('permissions/destroy/{permission}', 'destroy')->name('permissions.destroy');
    });

    Route::controller(AssignPermissionController::class)->group(function(){
        Route::get('assign-permissions', 'index')->name('assign-permissions.index');
        Route::get('assign-permissions/edit/{role}', 'edit')->name('assign-permissions.edit');
        Route::put('assign-permissions/edit/{role}', 'update')->name('assign-permissions.update');
    });

    Route::controller(AssignUserController::class)->group(function(){
        Route::get('assign-users', 'index')->name('assign-users.index');
        Route::get('assign-users/edit/{user}', 'edit')->name('assign-users.edit');
        Route::put('assign-users/edit/{user}', 'update')->name('assign-users.update');
    });

    Route::controller(RouteAccessController::class)->group(function(){
        Route::get('route-accesses', 'index')->name('route-accesses.index');
        Route::get('route-accesses/create', 'create')->name('route-accesses.create');
        Route::post('route-accesses/create', 'store')->name('route-accesses.store');
        Route::get('route-accesses/edit/{routeAccess}', 'edit')->name('route-accesses.edit');
        Route::put('route-accesses/edit/{routeAccess}', 'update')->name('route-accesses.update');
        Route::delete('route-accesses/destroy/{routeAccess}', 'destroy')->name('route-accesses.destroy');
    });


});
