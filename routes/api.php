<?php

use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DamageController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FieldSurveyController;
use App\Http\Controllers\Api\HandlingDocumentController;
use App\Http\Controllers\Api\RoadHandlingController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// ==========================================
// AUTENTIKASI
// ==========================================

Route::prefix('auth')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// ==========================================
// ADMIN PUPR
// ==========================================

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

        // Dashboard Admin
        Route::get('/dashboard', [DashboardController::class, 'admin']);

        // CRUD Petugas
        Route::apiResource('petugas', UserController::class);

        // CRUD Laporan Kerusakan
        Route::apiResource('damage-reports', DamageController::class);

        // Verifikasi Laporan
        Route::post(
            '/damage-reports/{damageReport}/verify',
            [DamageController::class, 'verify']
        );

        // CRUD Penugasan
        Route::apiResource('assignments', AssignmentController::class);

        // Melihat seluruh hasil survei
        Route::get(
            '/field-surveys',
            [FieldSurveyController::class, 'index']
        );

        // Melihat detail hasil survei
        Route::get(
            '/field-surveys/{fieldSurvey}',
            [FieldSurveyController::class, 'show']
        );

        // CRUD Penanganan Jalan
        Route::apiResource('road-handlings', RoadHandlingController::class
        );

        // Update Progres Road Handling
        Route::patch(
            '/road-handlings/{road_handling}/progress',
            [RoadHandlingController::class, 'updateProgress']
        )->name('road-handlings.update-progress'
        );

        // Handling Document
        Route::prefix('road-handlings/{road_handling}/documents')->group(function () {

            Route::get('/', [HandlingDocumentController::class, 'index'])
                ->name('handling-documents.index');

            Route::post('/', [HandlingDocumentController::class, 'store'])
                ->name('handling-documents.store');

            Route::get('/{handling_document}', [HandlingDocumentController::class, 'show'])
                ->name('handling-documents.show');

            Route::delete('/{handling_document}', [HandlingDocumentController::class, 'destroy'])
                ->name('handling-documents.destroy');

        });

    });

// ==========================================
// PETUGAS SURVEYOR
// ==========================================

Route::middleware(['auth:sanctum', 'role:petugas_surveyor'])
    ->prefix('petugas')
    ->group(function () {

        // Dashboard Petugas
        Route::get('/dashboard', [DashboardController::class, 'petugas']);

        // CRUD Laporan Milik Petugas
        Route::apiResource('damage-reports', DamageController::class);

        // Melihat tugas milik sendiri
        Route::get(
            '/assignments',
            [AssignmentController::class, 'myAssignments']
        );

        // Memperbarui status tugas
        Route::patch(
            '/assignments/{assignment}/status',
            [AssignmentController::class, 'updateStatus']
        );

        // Mengirim hasil survei
        Route::post(
            '/field-surveys',
            [FieldSurveyController::class, 'store']
        );

        // Melihat hasil survei milik sendiri
        Route::get(
            '/field-surveys',
            [FieldSurveyController::class, 'mySurveys']
        );
    });
