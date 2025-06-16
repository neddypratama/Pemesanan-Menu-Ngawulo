<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SSOController extends Controller
{
    public function sync(Request $request)
    {
        Log::error('SSO berhasil');
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role_id' => 'required|integer',
            'avatar' => 'nullable|string',
            'bio' => 'nullable|string',
            'password' => 'required',
        ]);
        // Cek apakah user sudah ada
        $user = User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'role_id' => $data['role_id'],
                'avatar' => $data['avatar'] ?? null,
                'bio' => $data['bio'] ?? '',
                'password' => Hash::make($data['password']),
            ]
        );
        Log::error('SSO berhasil'. $data);

        return response()->json(['message' => 'User synced'], 200);
    }

    public static function syncUser(User $user, string $plainPassword): void
    {
        Log::info('SSOController::syncUser dipanggil dengan user: ' . $user->email);
        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'avatar' => $user->avatar,
            'bio' => $user->bio,
            'password' => $plainPassword,
        ];

        try {
            Http::post('http://127.0.0.1:8000/api/sso/user-sync', $userData);
            Http::post('http://127.0.0.1:8001/api/sso/user-sync', $userData);
        } catch (\Exception $e) {
            Log::error('SSO Sync Failed: ' . $e->getMessage());
        }
    }

    
}
