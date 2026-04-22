<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Public/ProductIndex');
})->name('public.products.index');

Route::get('/product/{id}', function (int $id) {
    return Inertia::render('Public/ProductShow', [
        'productId' => $id,
    ]);
})->whereNumber('id')->name('public.products.show');

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('auth.login');

Route::middleware('admin.token')->group(function (): void {
    Route::get('/admin/products', function () {
        return Inertia::render('Admin/ProductIndex');
    })->name('admin.products.index');

    Route::get('/admin/products/create', function () {
        return Inertia::render('Admin/ProductForm', [
            'mode' => 'create',
            'productId' => null,
        ]);
    })->name('admin.products.create');

    Route::get('/admin/products/{id}/edit', function (int $id) {
        return Inertia::render('Admin/ProductForm', [
            'mode' => 'edit',
            'productId' => $id,
        ]);
    })->whereNumber('id')->name('admin.products.edit');
});
