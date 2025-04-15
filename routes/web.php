<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Users will be redirected to this route if not logged in
Route::middleware('guest')->group(function() {
    Volt::route('/login', 'auth.login')->name('login');
    Volt::route('/register', 'auth.register'); 
    Volt::route('/forgot-password', 'auth.forgot-password')->name('password.request');
    Volt::route('/reset-password/{token}', 'auth.password-reset')->name('password.reset');

});
 
// Define the logout
Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
 
    return redirect('/login');
});
 
// Protected routes here
Route::middleware('auth')->group(function () {
    Volt::route('/email/verify', 'auth.verify-email')->middleware('throttle:6,1')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill(); // Inilah yang menandai email sebagai terverifikasi
        return redirect('/')->with('success', 'Email berhasil diverifikasi!');
    })->middleware('signed')->name('verification.verify');

    Volt::route('/dashboard', 'dashboard')->name('dashboard');
    
    Volt::route('/users', 'users.index');
    Volt::route('/users/create', 'users.create');
    Volt::route('/users/{user}/edit', 'users.edit');

    Volt::route('/roles', 'roles.index');

    Volt::route('/categories', 'categories.index');

    Volt::route('/menus', 'menus.index');
    Volt::route('/menus/create', 'menus.create');
    Volt::route('/menus/{menu}/edit', 'menus.edit');

    Volt::route('/recipes', 'recipes.index');

    Volt::route('/ratings', 'ratings.index');

    Volt::route('/orders', 'orders.index');
    Volt::route('/orders/create', 'orders.create');
    Volt::route('/orders/{id}/edit', 'orders.edit')->name('orders.edit');
    Volt::route('/orders/{transaksi}/detail', 'orders.detail');

    Volt::route('/customers', 'customers.index');
    Volt::route('/customers/{customer}/detail', 'customers.detail');
    
    Volt::route('/detail/{menu}', 'detail')->name('detail');
    Volt::route('/', 'index');
    Volt::route('/my-orders', 'my-order');
    Volt::route('/my-orders/{transaksi}', 'detail-order')->name('orders.show');

    Volt::route('/cart', 'detail-cart');
    Volt::route('/checkout/{invoice}', 'checkout');
});

