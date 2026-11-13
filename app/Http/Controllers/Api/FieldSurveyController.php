<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\FieldSurvey;
use Illuminate\Http\Request;

class FieldSurveyController extends Controller
{
    // ==========================================
    // ADMIN: MELIHAT SELURUH HASIL SURVEI
    // ==========================================

    public function index()
    {
        $surveys = FieldSurvey::with([
            'assignment.surveyor:id,name,email',
            'assignment.damageReport',
            'assignment.assignedBy:id,name,email',
        ])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar hasil survei berhasil diambil.',
            'data' => $surveys,
        ]);
    }

    // ==========================================
    // PETUGAS: MEMBUAT HASIL SURVEI
    // ==========================================

    public function store(Request $request)
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:assignments,id',

            'road_condition' => 'required|in:good,moderate,poor',

            'inspection_result' => 'required|string',

            'recommendation' => 'nullable|string',

            'latitude' => 'nullable|numeric|between:-90,90',

            'longitude' => 'nullable|numeric|between:-180,180',

            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // Mengambil penugasan milik petugas yang login
        $assignment = Assignment::where('id', $validated['assignment_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Survei hanya dapat dibuat untuk tugas yang sedang dikerjakan
        if ($assignment->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Penugasan harus berstatus in_progress.',
            ], 422);
        }

        // Memastikan belum ada hasil survei sebelumnya
        if ($assignment->fieldSurvey()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Hasil survei untuk penugasan ini sudah tersedia.',
            ], 422);
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('field-surveys', 'public');
        }

        $survey = FieldSurvey::create([
            'assignment_id' => $assignment->id,
            'road_condition' => $validated['road_condition'],
            'inspection_result' => $validated['inspection_result'],
            'recommendation' => $validated['recommendation'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'image_path' => $imagePath,
            'surveyed_at' => now(),
        ]);

        // Menandai penugasan sebagai selesai
        $assignment->update([
            'status' => 'completed',
            'started_at' => $assignment->started_at ?? now(),
            'completed_at' => now(),
        ]);

        $survey->load([
            'assignment.surveyor:id,name,email',
            'assignment.damageReport',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hasil survei berhasil disimpan.',
            'data' => $survey,
        ], 201);
    }

    // ==========================================
    // ADMIN: MELIHAT DETAIL HASIL SURVEI
    // ==========================================

    public function show(FieldSurvey $fieldSurvey)
    {
        $fieldSurvey->load([
            'assignment.surveyor:id,name,email',
            'assignment.damageReport',
            'assignment.assignedBy:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail hasil survei berhasil diambil.',
            'data' => $fieldSurvey,
        ]);
    }

    // ==========================================
    // PETUGAS: MELIHAT HASIL SURVEI MILIKNYA
    // ==========================================

    public function mySurveys(Request $request)
    {
        $surveys = FieldSurvey::with([
            'assignment.damageReport',
        ])
            ->whereHas('assignment', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Hasil survei Anda berhasil diambil.',
            'data' => $surveys,
        ]);
    }
}
