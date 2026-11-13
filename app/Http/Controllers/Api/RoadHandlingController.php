<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoadHandling;
use Illuminate\Http\Request;

class RoadHandlingController extends Controller
{
    // Menampilkan seluruh data penanganan jalan
    public function index()
    {
        $handlings = RoadHandling::with([
            'damageReport',
            'creator',
            'documents',
        ])->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Data penanganan jalan berhasil ditampilkan.',
            'data' => $handlings,
        ], 200);
    }

    // Menambahkan data penanganan jalan
    public function store(Request $request)
    {
        $validated = $request->validate([
            'damage_report_id' => [
                'required',
                'exists:damage_reports,id',
            ],
            'action_type' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'start_date' => [
                'nullable',
                'date',
            ],
        ]);

        $handling = RoadHandling::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'progress' => 0,
            'status' => 'planned',
            'completion_date' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Penanganan jalan berhasil dibuat.',
            'data' => $handling,
        ], 201);
    }

    // Menampilkan detail penanganan jalan
    public function show($id)
    {
        $handling = RoadHandling::with([
            'damageReport',
            'creator',
            'documents.uploader',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail penanganan jalan berhasil ditampilkan.',
            'data' => $handling,
        ], 200);
    }

    // Memperbarui informasi penanganan jalan
    public function update(Request $request, $id)
    {
        $handling = RoadHandling::findOrFail($id);

        $validated = $request->validate([
            'action_type' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'start_date' => [
                'sometimes',
                'nullable',
                'date',
            ],
        ]);

        $handling->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data penanganan jalan berhasil diperbarui.',
            'data' => $handling,
        ], 200);
    }

    // Menghapus data penanganan jalan
    public function destroy($id)
    {
        $handling = RoadHandling::findOrFail($id);

        $handling->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data penanganan jalan berhasil dihapus.',
        ], 200);
    }

    // 6. Memperbarui progres dan status pekerjaan
    public function updateProgress(Request $request, RoadHandling $road_handling)
    {
        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
            'status' => 'required|in:planned,in_progress,completed',
            'completion_date' => 'nullable|date|required_if:status,completed',
        ]);

        // Jika pekerjaan sudah selesai, progres harus 100%
        if (
            $validated['status'] === 'completed' &&
            $validated['progress'] !== 100
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Progres harus 100% jika pekerjaan sudah selesai.',
            ], 422);
        }

        // Perbarui data progres
        $road_handling->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Progres pekerjaan berhasil diperbarui.',
            'data' => $road_handling->fresh(),
        ], 200);
    }
}
