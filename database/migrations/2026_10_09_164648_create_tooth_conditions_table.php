<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tooth_conditions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_tooth_id')
                ->constrained('patient_teeth')
                ->cascadeOnDelete();

            $table->foreignId('visit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('condition_type');

            $table->json('surfaces')->nullable();

            $table->text('notes')->nullable();

            $table->timestamp('recorded_at');

            $table->timestamps();

            $table->index([
                'patient_tooth_id',
                'recorded_at',
            ]);

            $table->index('visit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tooth_conditions');
    }
};