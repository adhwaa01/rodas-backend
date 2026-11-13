<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Login Admin PUPR dan Petugas Surveyor
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:100',
        ]);

        $user = User::with('role')
            ->where('email', $validated['email'])
            ->first();

        if (
            ! $user ||
            ! Hash::check($validated['password'], $user->password)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password tidak sesuai.',
            ], 401);
        }

        if (! $user->role) {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum memiliki role.',
            ], 403);
        }

        $token = $user->createToken(
            $validated['device_name']
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
                'display_role' => $user->role->display_name,
            ],
        ], 200);
    }

    // Melihat profil pengguna yang sedang login
    public function profile(Request $request)
    {
        $user = $request->user()->load('role');

        return response()->json([
            'success' => true,
            'message' => 'Profil pengguna berhasil diambil.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->name,
                'display_role' => $user->role?->display_name,
            ],
        ], 200);
    }

    // Logout dan menghapus token yang sedang digunakan
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ], 200);
    }
}
