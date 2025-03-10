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
 
    return redirect('/');
});
 
// Protected routes here
Route::middleware('auth')->group(function () {
    Volt::route('/email/verify', 'auth.verify-email')->middleware('throttle:6,1')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill(); // Inilah yang menandai email sebagai terverifikasi
        return redirect('/')->with('success', 'Email berhasil diverifikasi!');
    })->middleware('signed')->name('verification.verify');

    Volt::route('/', 'index')->name('dashboard');
    
    Volt::route('/users', 'users.index');
    Volt::route('/users/create', 'users.create');
    Volt::route('/users/{user}/edit', 'users.edit');

    Volt::route('/roles', 'roles.index');
    Volt::route('/roles/create', 'roles.create');
    Volt::route('/roles/{role}/edit', 'roles.edit');
    // ... more
});
