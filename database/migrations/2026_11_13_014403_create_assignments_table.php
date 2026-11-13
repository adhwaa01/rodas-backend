<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();

            // Petugas yang menerima tugas
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Laporan kerusakan yang ditugaskan
            $table->foreignId('damage_report_id')
                ->constrained('damage_reports')
                ->cascadeOnDelete();

            // Admin yang memberikan tugas
            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Instruksi pekerjaan
            $table->text('instructions')->nullable();

            // Batas waktu pengerjaan
            $table->date('due_date')->nullable();

            // Status penugasan
            $table->enum('status', [
                'assigned',
                'in_progress',
                'completed',
                'cancelled',
            ])->default('assigned');

            // Waktu mulai dan selesai
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
