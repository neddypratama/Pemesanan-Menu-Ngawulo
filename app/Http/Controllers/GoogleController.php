<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function google_redirect() {
        return Socialite::driver('google')->redirect();
    }
        
    public function google_callback() {
        $googleUser = Socialite::driver('google')->stateless()->user(); // ← tambahkan stateless()
    
        $user = User::whereEmail($googleUser->email)->first();
    
        if (!$user) {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'no_hp' => null,
                'email_verified_at' => now(),
                'password' => Hash::make('password123'), // default password
                'role_id' => 4,
                'avatar' => $googleUser->getAvatar(),
                'bio' => 'Pembeli'
            ]);
        }
    
        Auth::login($user);
    
        return redirect('/')->with('success', $user->wasRecentlyCreated
            ? 'Akun berhasil dibuat! Selamat datang 🎉'
            : 'Akun berhasil masuk! Selamat datang 🎉');
    }
    
}
