<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('clinic_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('patient_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('doctor_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('visit_type_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('appointment_id')
                ->nullable()
                ->unique()
                ->constrained()
                ->nullOnDelete();

            $table->string('status')->default('open');

            $table->text('chief_complaint')->nullable();
            $table->text('examination_notes')->nullable();

            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index([
                'patient_id',
                'started_at',
            ]);

            $table->index([
                'doctor_profile_id',
                'started_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};