<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handling_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('road_handling_id')
                ->constrained('road_handlings')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('file_path');
            $table->text('description')->nullable();

            $table->enum('document_type', [
                'before',
                'progress',
                'result',
            ])->default('progress');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handling_documents');
    }
};
