<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DamageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = DamageReport::query()
            ->with([
                'user:id,name,email',
                'verifier:id,name,email',
            ]);

        if ($user->role?->name === 'petugas_surveyor') {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar laporan kerusakan jalan berhasil diambil.',
            'data' => $query->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'damage_type' => ['required', 'string', 'max:255'],
            'severity' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $imagePath = $request->hasFile('image')
            ? $this->storeImage($request->file('image'))
            : null;

        $report = DamageReport::create([
            'user_id' => $request->user()->id,
            'damage_type' => $validated['damage_type'],
            'severity' => $validated['severity'] ?? null,
            'description' => $validated['description'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'image_path' => $imagePath,
            'status' => 'pending',
        ]);

        $report->load('user:id,name,email');

        return response()->json([
            'success' => true,
            'message' => 'Laporan kerusakan jalan berhasil dibuat.',
            'data' => $report,
        ], 201);
    }

    public function show(Request $request, DamageReport $damageReport)
    {
        $this->ensureCanAccess($request, $damageReport);
        $damageReport->load([
            'user:id,name,email',
            'verifier:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail laporan berhasil diambil.',
            'data' => $damageReport,
        ]);
    }

    public function update(Request $request, DamageReport $damageReport)
    {
        $this->ensureCanAccess($request, $damageReport);

        $isAdmin = $request->user()->role?->name === 'admin';
        $rules = [
            'damage_type' => ['sometimes', 'required', 'string', 'max:255'],
            'severity' => ['sometimes', 'nullable', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];

        if ($isAdmin) {
            $rules['status'] = [
                'sometimes',
                'required',
                Rule::in(['pending', 'verified', 'rejected']),
            ];
            $rules['verification_note'] = ['sometimes', 'nullable', 'string'];
        }

        $validated = $request->validate($rules);
        $oldImagePath = $damageReport->image_path;

        if ($request->hasFile('image')) {
            $validated['image_path'] = $this->storeImage(
                $request->file('image')
            );
        }

        unset($validated['image']);

        if ($isAdmin && array_key_exists('status', $validated)) {
            $isDecision = in_array(
                $validated['status'],
                ['verified', 'rejected'],
                true
            );
            $validated['verified_by'] = $isDecision
                ? $request->user()->id
                : null;
            $validated['verified_at'] = $isDecision ? now() : null;
        }

        $damageReport->update($validated);

        if (
            $damageReport->wasChanged('image_path') &&
            $oldImagePath
        ) {
            $this->deleteImage($oldImagePath);
        }

        $damageReport->load([
            'user:id,name,email',
            'verifier:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan kerusakan jalan berhasil diperbarui.',
            'data' => $damageReport,
        ]);
    }

    public function verify(Request $request, DamageReport $damageReport)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['verified', 'rejected']),
            ],
            'verification_note' => ['nullable', 'string'],
        ]);

        $damageReport->update([
            'status' => $validated['status'],
            'verification_note' => $validated['verification_note'] ?? null,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);
        $damageReport->load([
            'user:id,name,email',
            'verifier:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan kerusakan berhasil diverifikasi.',
            'data' => $damageReport,
        ]);
    }

    public function destroy(Request $request, DamageReport $damageReport)
    {
        $this->ensureCanAccess($request, $damageReport);

        $imagePath = $damageReport->image_path;
        $damageReport->delete();

        if ($imagePath) {
            $this->deleteImage($imagePath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan kerusakan jalan berhasil dihapus.',
        ]);
    }

    private function ensureCanAccess(
        Request $request,
        DamageReport $damageReport
    ): void {
        if (
            $request->user()->role?->name === 'petugas_surveyor' &&
            $damageReport->user_id !== $request->user()->id
        ) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }
    }

    private function storeImage(UploadedFile $image): string
    {
        $path = $image->store('damage-reports', 'public');

        if ($path === false) {
            abort(500, 'Foto laporan kerusakan gagal disimpan.');
        }

        return $path;
    }

    private function deleteImage(string $path): void
    {
        if (! Storage::disk('public')->delete($path)) {
            abort(500, 'Foto laporan kerusakan gagal dihapus.');
        }
    }
}
