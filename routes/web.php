<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])
    ->name('home');
Route::get('products', [\App\Http\Controllers\ProductController::class, 'index'])
    ->name('products.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('cart', [\App\Http\Controllers\CartController::class, 'store'])->name('cart.store');
    Route::post('cart/checkout', [\App\Http\Controllers\CartController::class, 'checkout'])->name('cart.checkout');
    Route::put('cart/items/{cartItem}', [\App\Http\Controllers\CartController::class, 'update'])->name('cart.items.update');
    Route::delete('cart/items/{cartItem}', [\App\Http\Controllers\CartController::class, 'destroy'])->name('cart.items.destroy');
});

require __DIR__ . '/settings.php';
