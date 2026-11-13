<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Menampilkan seluruh akun Petugas Surveyor
    public function index()
    {
        $petugas = User::with('role')
            ->whereHas('role', function ($query) {
                $query->where('name', 'petugas_surveyor');
            })
            ->latest()
            ->get()
            ->makeHidden(['password', 'remember_token']);

        return response()->json([
            'success' => true,
            'message' => 'Daftar Petugas Surveyor berhasil diambil.',
            'data' => $petugas,
        ]);
    }

    // Menambahkan akun Petugas Surveyor
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $role = Role::where('name', 'petugas_surveyor')
            ->firstOrFail();

        $petugas = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
        ]);

        $petugas->load('role');

        return response()->json([
            'success' => true,
            'message' => 'Akun Petugas Surveyor berhasil dibuat.',
            'data' => $petugas->makeHidden([
                'password',
                'remember_token',
            ]),
        ], 201);
    }

    // Menampilkan detail Petugas Surveyor
    public function show(User $petugas)
    {
        abort_unless(
            $petugas->role?->name === 'petugas_surveyor',
            404
        );

        $petugas->load('role');

        return response()->json([
            'success' => true,
            'message' => 'Detail Petugas Surveyor berhasil diambil.',
            'data' => $petugas->makeHidden([
                'password',
                'remember_token',
            ]),
        ]);
    }

    // Mengubah data Petugas Surveyor
    public function update(Request $request, User $petugas)
    {
        abort_unless(
            $petugas->role?->name === 'petugas_surveyor',
            404
        );

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($petugas->id),
            ],
            'password' => 'sometimes|required|string|min:8',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make(
                $validated['password']
            );
        }

        $petugas->update($validated);
        $petugas->load('role');

        return response()->json([
            'success' => true,
            'message' => 'Data Petugas Surveyor berhasil diperbarui.',
            'data' => $petugas->makeHidden([
                'password',
                'remember_token',
            ]),
        ]);
    }

    // Menghapus akun Petugas Surveyor
    public function destroy(User $petugas)
    {
        abort_unless(
            $petugas->role?->name === 'petugas_surveyor',
            404
        );

        $petugas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun Petugas Surveyor berhasil dihapus.',
        ]);
    }
}
