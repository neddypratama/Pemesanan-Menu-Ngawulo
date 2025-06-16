<?php

use App\Http\Controllers\GoogleController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::view('/bayar', 'bayar');


// ======================
// 👤 GUEST ROUTES
// ======================
Route::middleware('guest')->group(function () {
    Volt::route('/login', 'auth.login')->name('login');
    Volt::route('/login-sso', 'auth.login-sso')->name('login-sso');
    Volt::route('/register', 'auth.register');
    Volt::route('/forgot-password', 'auth.forgot-password')->name('password.request');
    Volt::route('/reset-password/{token}', 'auth.password-reset')->name('password.reset');
    Route::get('/auth-google-redirect', [GoogleController::class, 'google_redirect'])->name('google-redirect');
    Route::get('/auth-google-callback', [GoogleController::class, 'google_callback'])->name('google-callback');
});

// ======================
// 🔓 LOGOUT
// ======================

Route::get('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
});



// ======================
// 🔐 AUTHENTICATED ROUTES
// ======================
Route::middleware('auth')->group(function () {

    Volt::route('/dashboard-sso', 'dashboard-sso')->name('dashboard-sso');
    // 📧 EMAIL VERIFICATION
    Volt::route('/profile', 'auth.ubahprofile');
    Volt::route('/email/verify', 'auth.verify-email')->middleware('throttle:6,1')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/')->with('success', 'Email berhasil diverifikasi!');
    })->middleware('signed')->name('verification.verify');

    Route::middleware('role:1,2,3')->group(function() {
        Volt::route('/dashboard', 'dashboard');
    });

    // ======================
    // 👤 ROLE: 4 (Pelanggan)
    // ======================
    Route::middleware('role:4')->group(function () {
        Volt::route('/', 'index')->name('index');
        Volt::route('/detail/{menu}', 'detail')->name('detail');
        Volt::route('/my-orders', 'my-order');
        Volt::route('/my-orders/{transaksi}', 'detail-order')->name('orders.show');
        Volt::route('/cart', 'detail-cart');
        Volt::route('/checkout/{invoice}', 'checkout');
    });

    // ======================
    // 👨‍💼 ROLE: 1, 2 (Admin & Kasir)
    // ======================
    Route::middleware('role:1,2')->group(function () {
        Volt::route('/categories', 'categories.index');

        Volt::route('/menus/create', 'menus.create');
        Volt::route('/menus', 'menus.index');
        Volt::route('/menus/{menu}/edit', 'menus.edit');

        Volt::route('/ratings', 'ratings.index');

        Volt::route('/recipes', 'recipes.index');

        Volt::route('/orders', 'orders.index');
        Volt::route('/orders/create', 'orders.create');
        Volt::route('/orders/{id}/edit', 'orders.edit')->name('orders.edit');
        Volt::route('/orders/{transaksi}/detail', 'orders.detail');

        Volt::route('/customers', 'customers.index');
        Volt::route('/customers/{customer}/detail', 'customers.detail');
    });

    // ======================
    // 🛠️ ROLE: 1 ONLY (Admin)
    // ======================
    Route::middleware('role:1')->group(function () {
        Volt::route('/users', 'users.index');
        Volt::route('/users/create', 'users.create');
        Volt::route('/users/{user}/edit', 'users.edit');
        Volt::route('/roles', 'roles.index');
    });

    // ======================
    // 🛠️ ROLE: 3 ONLY (Kasir)
    // ======================
    Route::middleware('role:3')->group(function () {
        Volt::route('/stok', 'stoks.index');
        Volt::route('/transaksi', 'transactions.index');
        Volt::route('/resep', 'resep.index');
    });
});
