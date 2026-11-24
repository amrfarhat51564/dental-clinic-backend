<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_diagnoses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('patient_tooth_id')
                ->nullable()
                ->constrained('patient_teeth')
                ->nullOnDelete();

            $table->string('diagnosis');
            $table->text('notes')->nullable();

            $table->timestamp('diagnosed_at');

            $table->timestamps();

            $table->index('visit_id');

            $table->index([
                'patient_tooth_id',
                'diagnosed_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_diagnoses');
    }
};