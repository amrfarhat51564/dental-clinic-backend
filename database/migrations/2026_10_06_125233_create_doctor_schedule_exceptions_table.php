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
        Schema::create('doctor_schedule_exceptions', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('doctor_profile_id')
                ->constrained()
                ->cascadeOnDelete();
        
            $table->date('date');
        
            $table->boolean('is_available')->default(false);
        
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
        
            $table->string('reason')->nullable();
        
            $table->timestamps();
        
            $table->index([
                'doctor_profile_id',
                'date',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_schedule_exceptions');
    }
};
