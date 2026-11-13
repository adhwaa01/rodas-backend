<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_surveys', function (Blueprint $table) {
            $table->id();

            // Relasi dengan penugasan
            $table->foreignId('assignment_id')
                ->unique()
                ->constrained('assignments')
                ->cascadeOnDelete();

            // Hasil pemeriksaan kondisi jalan
            $table->string('road_condition');
            $table->text('inspection_result');

            // Rekomendasi penanganan
            $table->text('recommendation')->nullable();

            // Koordinat lokasi survei
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Foto dokumentasi
            $table->string('image_path')->nullable();

            // Waktu pelaksanaan survei
            $table->timestamp('surveyed_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_surveys');
    }
};
