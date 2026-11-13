<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HandlingDocument;
use App\Models\RoadHandling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HandlingDocumentController extends Controller
{
    // 1. Menampilkan seluruh dokumentasi pekerjaan jalan
    public function index(RoadHandling $road_handling)
    {
        $documents = $road_handling->documents()
            ->with('uploader')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dokumentasi berhasil ditampilkan.',
            'data' => $documents,
        ], 200);
    }

    // 2. Upload dokumentasi pekerjaan jalan
    public function store(Request $request, RoadHandling $road_handling)
    {
        $validated = $request->validate([
            'document' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'description' => 'nullable|string',
            'document_type' => 'required|in:before,progress,result',
        ]);

        // Simpan file ke storage/app/public/handling-documents
        $filePath = $request->file('document')
            ->store('handling-documents', 'public');

        if ($filePath === false) {
            abort(500, 'Dokumentasi gagal disimpan.');
        }

        $document = $road_handling->documents()->create([
            'uploaded_by' => $request->user()->id,
            'file_path' => $filePath,
            'description' => $validated['description'] ?? null,
            'document_type' => $validated['document_type'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumentasi berhasil diunggah.',
            'data' => $document->load('uploader'),
            'file_url' => asset('storage/'.$filePath),
        ], 201);
    }

    // 3. Menampilkan detail dokumentasi
    public function show(
        RoadHandling $road_handling,
        HandlingDocument $handling_document
    ) {
        // Memastikan dokumen milik pekerjaan jalan yang sesuai
        if ($handling_document->road_handling_id !== $road_handling->id) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumentasi tidak ditemukan pada pekerjaan ini.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail dokumentasi berhasil ditampilkan.',
            'data' => $handling_document->load('uploader'),
            'file_url' => asset('storage/'.$handling_document->file_path),
        ], 200);
    }

    // 4. Menghapus dokumentasi dan file foto
    public function destroy(
        RoadHandling $road_handling,
        HandlingDocument $handling_document
    ) {
        // Memastikan dokumen milik pekerjaan jalan yang sesuai
        if ($handling_document->road_handling_id !== $road_handling->id) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumentasi tidak ditemukan pada pekerjaan ini.',
            ], 404);
        }

        // Hapus file dari storage
        if (! Storage::disk('public')->delete($handling_document->file_path)) {
            abort(500, 'File dokumentasi gagal dihapus.');
        }

        // Hapus data dokumentasi dari database
        $handling_document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dokumentasi dan file foto berhasil dihapus.',
        ], 200);
    }
}
