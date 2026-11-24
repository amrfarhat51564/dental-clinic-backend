<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
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
        
            $table->dateTime('start_at');
            $table->dateTime('end_at');
        
            $table->string('status')->default('pending');
        
            $table->text('notes')->nullable();
        
            $table->timestamps();
        
            $table->index(['patient_id', 'start_at']);
            $table->index(['doctor_profile_id', 'status', 'start_at']);
            $table->index(['doctor_profile_id', 'start_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
