<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('road_handlings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('damage_report_id')
                ->constrained('damage_reports')
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('action_type');
            $table->text('description')->nullable();

            $table->date('start_date')->nullable();

            $table->unsignedTinyInteger('progress')->default(0);

            $table->enum('status', [
                'planned',
                'in_progress',
                'completed',
                'cancelled',
            ])->default('planned');

            $table->date('completion_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('road_handlings');
    }
};
