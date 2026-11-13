<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();

            // Relasi dengan akun Petugas Surveyor
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Informasi kerusakan jalan
            $table->string('damage_type');
            $table->string('severity')->nullable();
            $table->text('description')->nullable();

            // Koordinat lokasi kerusakan
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Foto dokumentasi kerusakan
            $table->string('image_path')->nullable();

            // Status laporan
            $table->enum('status', [
                'pending',
                'verified',
                'rejected',
            ])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
    }
};
