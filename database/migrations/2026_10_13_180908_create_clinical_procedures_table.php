<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_procedures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('procedure_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('patient_tooth_id')
                ->nullable()
                ->constrained('patient_teeth')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamp('performed_at');

            $table->timestamps();

            $table->index([
                'visit_id',
                'performed_at',
            ]);

            $table->index('procedure_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_procedures');
    }
};