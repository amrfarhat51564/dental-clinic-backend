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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('clinic_id')
                ->constrained()
                ->cascadeOnDelete();
        
            $table->string('patient_code')->unique();
        
            $table->string('full_name');
            $table->string('phone')->nullable()->index();
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();
        
            $table->enum('gender', ['male', 'female'])->nullable();
        
            $table->string('address')->nullable();
        
            $table->timestamps();
        
            $table->index('clinic_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
