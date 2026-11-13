<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DamageReport;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Dashboard Admin PUPR
    public function admin()
    {
        $totalPetugas = User::whereHas('role', function ($query) {
            $query->where('name', 'petugas_surveyor');
        })->count();

        $totalLaporan = DamageReport::count();

        $pending = DamageReport::where('status', 'pending')->count();

        $verified = DamageReport::where('status', 'verified')->count();

        $rejected = DamageReport::where('status', 'rejected')->count();

        $laporanTerbaru = DamageReport::with('user:id,name,email')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard Admin berhasil diambil.',
            'data' => [
                'total_petugas' => $totalPetugas,
                'total_laporan' => $totalLaporan,
                'laporan_pending' => $pending,
                'laporan_verified' => $verified,
                'laporan_rejected' => $rejected,
                'laporan_terbaru' => $laporanTerbaru,
            ],
        ]);
    }

    // Dashboard Petugas Surveyor
    public function petugas(Request $request)
    {
        $userId = $request->user()->id;

        $query = DamageReport::where('user_id', $userId);

        $totalLaporan = (clone $query)->count();

        $pending = (clone $query)
            ->where('status', 'pending')
            ->count();

        $verified = (clone $query)
            ->where('status', 'verified')
            ->count();

        $rejected = (clone $query)
            ->where('status', 'rejected')
            ->count();

        $laporanTerbaru = (clone $query)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard Petugas berhasil diambil.',
            'data' => [
                'total_laporan' => $totalLaporan,
                'laporan_pending' => $pending,
                'laporan_verified' => $verified,
                'laporan_rejected' => $rejected,
                'laporan_terbaru' => $laporanTerbaru,
            ],
        ]);
    }
}
