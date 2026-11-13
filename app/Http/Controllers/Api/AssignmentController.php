<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    // ==========================================
    // ADMIN: MENAMPILKAN SELURUH PENUGASAN
    // ==========================================

    public function index()
    {
        $assignments = Assignment::with([
            'surveyor:id,name,email',
            'damageReport',
            'assignedBy:id,name,email',
        ])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar penugasan berhasil diambil.',
            'data' => $assignments,
        ]);
    }

    // ==========================================
    // ADMIN: MEMBUAT PENUGASAN
    // ==========================================

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'damage_report_id' => 'required|exists:damage_reports,id',
            'instructions' => 'nullable|string',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        // Memastikan penerima tugas adalah Petugas Surveyor
        $surveyor = User::with('role')
            ->findOrFail($validated['user_id']);

        if ($surveyor->role?->name !== 'petugas_surveyor') {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna yang dipilih bukan Petugas Surveyor.',
            ], 422);
        }

        $assignment = Assignment::create([
            'user_id' => $validated['user_id'],
            'damage_report_id' => $validated['damage_report_id'],
            'assigned_by' => $request->user()->id,
            'instructions' => $validated['instructions'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'status' => 'assigned',
        ]);

        $assignment->load([
            'surveyor:id,name,email',
            'damageReport',
            'assignedBy:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Penugasan berhasil dibuat.',
            'data' => $assignment,
        ], 201);
    }

    // ==========================================
    // ADMIN: MELIHAT DETAIL PENUGASAN
    // ==========================================

    public function show(Assignment $assignment)
    {
        $assignment->load([
            'surveyor:id,name,email',
            'damageReport',
            'assignedBy:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail penugasan berhasil diambil.',
            'data' => $assignment,
        ]);
    }

    // ==========================================
    // ADMIN: MEMPERBARUI PENUGASAN
    // ==========================================

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'damage_report_id' => 'sometimes|required|exists:damage_reports,id',
            'instructions' => 'sometimes|nullable|string',
            'due_date' => 'sometimes|nullable|date|after_or_equal:today',
        ]);

        if (isset($validated['user_id'])) {
            $surveyor = User::with('role')
                ->findOrFail($validated['user_id']);

            if ($surveyor->role?->name !== 'petugas_surveyor') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna yang dipilih bukan Petugas Surveyor.',
                ], 422);
            }
        }

        $assignment->update($validated);

        $assignment->load([
            'surveyor:id,name,email',
            'damageReport',
            'assignedBy:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Penugasan berhasil diperbarui.',
            'data' => $assignment,
        ]);
    }

    // ==========================================
    // ADMIN: MENGHAPUS PENUGASAN
    // ==========================================

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Penugasan berhasil dihapus.',
        ]);
    }

    // ==========================================
    // PETUGAS: MELIHAT DAFTAR TUGAS MILIKNYA
    // ==========================================

    public function myAssignments(Request $request)
    {
        $assignments = Assignment::with([
            'damageReport',
            'assignedBy:id,name,email',
        ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar tugas Anda berhasil diambil.',
            'data' => $assignments,
        ]);
    }

    // ==========================================
    // PETUGAS: MEMPERBARUI STATUS TUGAS
    // ==========================================

    public function updateStatus(
        Request $request,
        Assignment $assignment
    ) {
        // Memastikan tugas milik petugas yang login
        if ($assignment->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke penugasan ini.');
        }

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['in_progress', 'completed']),
            ],
        ]);

        // Tugas yang sudah dibatalkan atau selesai tidak dapat diubah
        if (! in_array($assignment->status, ['assigned', 'in_progress'])) {
            return response()->json([
                'success' => false,
                'message' => 'Status penugasan tidak dapat diubah lagi.',
            ], 422);
        }

        $status = $validated['status'];

        $data = [
            'status' => $status,
        ];

        if ($status === 'in_progress' && ! $assignment->started_at) {
            $data['started_at'] = now();
        }

        if ($status === 'completed') {
            $data['started_at'] ??= now();
            $data['completed_at'] = now();
        }

        $assignment->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Status penugasan berhasil diperbarui.',
            'data' => $assignment,
        ]);
    }
}
